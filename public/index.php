<?php

declare(strict_types=1);

namespace Restaurante;

use Restaurante\Config\Config;
use Restaurante\Database\Conexion;
use Restaurante\Logger;
use Restaurante\Dao\AreaDao;
use Restaurante\Dao\DetallePedidoDao;
use Restaurante\Dao\IntentosLoginDao;
use Restaurante\Dao\MesaDao;
use Restaurante\Dao\PedidoDao;
use Restaurante\Dao\ProductoDao;
use Restaurante\Dao\ReservaDao;
use Restaurante\Dao\UsuarioDao;
use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Middleware\RateLimiterMiddleware;
use Restaurante\Service\ServicioDeAutenticacion;
use Restaurante\Service\ServicioDeMesas;
use Restaurante\Service\ServicioDePedidos;
use Restaurante\Service\ServicioDeProductos;
use Restaurante\Service\ServicioDeReservas;
use Restaurante\Validator\ValidadorDeReserva;
use Restaurante\Controller\AreaAdminController;
use Restaurante\Controller\AreaController;
use Restaurante\Controller\AuthController;
use Restaurante\Controller\MesaAdminController;
use Restaurante\Controller\MesaController;
use Restaurante\Controller\PedidoController;
use Restaurante\Controller\ProductoController;
use Restaurante\Controller\ReservaController;
use Restaurante\Controller\UsuarioAdminController;

// ---------------------------------------------------------------------------
// Autoloader simple: Restaurante\Xxx\Yyy -> src/Xxx/Yyy.php
// ---------------------------------------------------------------------------
spl_autoload_register(function (string $clase): void {
    $prefijo = 'Restaurante\\';
    if (str_starts_with($clase, $prefijo)) {
        $rutaRelativa = str_replace('\\', DIRECTORY_SEPARATOR, substr($clase, strlen($prefijo))) . '.php';
        $archivo = __DIR__ . '/../src/' . $rutaRelativa;
        if (is_file($archivo)) {
            require $archivo;
        }
    }
});

$envFile = __DIR__ . '/../.env';
$esProduccion = (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
    || (getenv('APP_ENV') && getenv('APP_ENV') === 'production')
    || (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'production');
if (is_file($envFile) && !$esProduccion) {
    $lineas = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }
        if (str_contains($linea, '=')) {
            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor, "\"'");
            // Solo establecer desde .env si no existe ya en el entorno.
            if (!isset($_ENV[$clave]) && !getenv($clave)) {
                $_ENV[$clave] = $valor;
                putenv(sprintf('%s=%s', $clave, $valor));
            }
        }
    }
}

$logDir = $_ENV['LOG_DIR'] ?? __DIR__ . '/../logs';
Logger::init($logDir);

ConfiguracionSesion::aplicar();

// Leer cuerpo de la petición una sola vez y dejarlo disponible para todos los controladores
if (!isset($_SERVER['RAW_INPUT'])) {
    $_SERVER['RAW_INPUT'] = @file_get_contents('php://input');
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$appUrl = Config::appUrl();
$rutaBase = rtrim(parse_url($appUrl, PHP_URL_PATH) ?? '/', '/');

if ($rutaBase !== '/' && str_starts_with($uri, $rutaBase)) {
    $uri = substr($uri, strlen($rutaBase));
}
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

$usuarioDao = new UsuarioDao();
$intentosDao = new IntentosLoginDao();
$areaDao = new AreaDao();
$mesaDao = new MesaDao();
$pedidoDao = new PedidoDao();
$reservaDao = new ReservaDao();
$productoDao = new ProductoDao();
$detallePedidoDao = new DetallePedidoDao();
$auth = new ServicioDeAutenticacion($usuarioDao, $intentosDao);
$servicioDeMesas = new ServicioDeMesas($areaDao, $mesaDao);
$validadorDeReserva = new ValidadorDeReserva($reservaDao, $mesaDao);
$servicioDeReservas = new ServicioDeReservas($reservaDao, $validadorDeReserva);
$servicioDePedidos = new ServicioDePedidos($pedidoDao, $productoDao, $mesaDao);
$servicioDeProductos = new ServicioDeProductos($productoDao);
$authMiddleware = new AuthMiddleware($auth);
$rateLimiter = new RateLimiterMiddleware();

$authController = new AuthController($auth, $authMiddleware, $rateLimiter, $usuarioDao);
$areaController = new AreaController($servicioDeMesas);
$mesaController = new MesaController($servicioDeMesas);
$reservaController = new ReservaController($servicioDeReservas, $authMiddleware);
$pedidoController = new PedidoController($authMiddleware);
$productoController = new ProductoController($servicioDeProductos);
$usuarioAdminController = new UsuarioAdminController($usuarioDao, $authMiddleware, $auth);
$areaAdminController = new AreaAdminController($areaDao, $authMiddleware);
$mesaAdminController = new MesaAdminController($mesaDao, $authMiddleware);

function leerCuerpoJson(): array
{
    // Usar primero el cuerpo guardado por el router (php://input solo se lee una vez)
    if (isset($_SERVER['RAW_INPUT']) && $_SERVER['RAW_INPUT'] !== '') {
        $cuerpo = $_SERVER['RAW_INPUT'];
    } else {
        $cuerpo = @file_get_contents('php://input');
        if ($cuerpo === false || empty($cuerpo)) {
            $handle = @fopen('php://input', 'r');
            if ($handle) {
                $cuerpo = '';
                while (!feof($handle)) {
                    $fragmento = fread($handle, 8192);
                    if ($fragmento === false) {
                        break;
                    }
                    $cuerpo .= $fragmento;
                }
                fclose($handle);
            }
        }
    }

    $datos = json_decode($cuerpo, true);

    return is_array($datos) ? $datos : [];
}

$respuesta = null;
$codigo_http = 200;

try {
    // â”€â”€ Servir archivos estÃ¡ticos PRIMERO (antes de tocar la DB) â”€â”€
    // Evita que .js/.css se sirvan como text/html cuando la BD no estÃ¡ disponible
    $extensionesEstaticas = ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'webp'];
    $extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if ($extension !== '' && in_array($extension, $extensionesEstaticas, true)) {
        $archivoFisico = __DIR__ . $uri;
        if (is_file($archivoFisico)) {
            $mimeTypes = [
                'js' => 'application/javascript',
                'css' => 'text/css',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'font/eot',
                'webp' => 'image/webp',
            ];
            $mime = $mimeTypes[$extension] ?? 'application/octet-stream';
            http_response_code(200);
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=31536000');
            echo file_get_contents($archivoFisico);
            exit;
        }
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Archivo no encontrado: ' . $uri, 'codigo' => 404], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = Conexion::obtener();
    $pdo->query('SELECT 1');

    // Verificar que las tablas existan
    $tablas = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetch();
    if (!$tablas) {
        echo json_encode(['exito' => false, 'mensaje' => 'Aplicando migraciones, espere...', 'codigo' => 202], JSON_UNESCAPED_UNICODE);
        aplicarMigraciones($pdo);
        echo json_encode(['exito' => true, 'mensaje' => 'Migraciones aplicadas. Recargue la pÃ¡gina.', 'codigo' => 200], JSON_UNESCAPED_UNICODE);
        exit;
    }

    switch (true) {
        case $uri === '/':
            $htmlFile = __DIR__ . '/index.html';
            if ($metodo === 'GET' && is_file($htmlFile)) {
                http_response_code(200);
                echo file_get_contents($htmlFile);
                exit;
            }
            $respuesta = [
                'exito' => true,
                'aplicacion' => 'Sistema de gestiÃ³n de un restaurante',
                'version' => '1.0.0-completo',
                'entorno' => Config::obtener('APP_ENV'),
                'base_de_datos' => 'conectada',
                'ruta' => $uri,
                'metodo' => $metodo,
                'mensaje' => 'Sistema completo: autenticaciÃ³n, mesas, reservas, pedidos, productos, administraciÃ³n.',
            ];
            break;

        case str_starts_with($uri, '/api/auth'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/auth')), '/');

            if (in_array($ruta_interna, ['login', 'registro'], true) && $metodo === 'POST') {
                $cliente_id = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
                $endpoint = $metodo . ':' . $uri;
                $rateLimiter->consumir($cliente_id, $endpoint);
            }

            $respuesta = $authController->manejar($ruta_interna, $metodo);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/usuario'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/usuario')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();

            if ($ruta_interna === 'perfil' && $metodo === 'GET') {
                $authMiddleware->requiereAutenticacion();
                $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
                $usuario = $usuarioDao->obtenerPorId($id_usuario);
                if ($usuario === null) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Usuario no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $respuesta = [
                    'exito' => true,
                    'datos' => [
                        'id_usuario' => $usuario->id_usuario,
                        'nombre' => $usuario->nombre,
                        'email' => $usuario->email,
                        'id_rol' => $usuario->id_rol,
                        'rol_nombre' => $usuario->rol_nombre ?? null,
                        'activo' => $usuario->activo,
                        'fecha_creacion' => $usuario->fecha_creacion,
                        'fecha_actualizacion' => $usuario->fecha_actualizacion,
                    ],
                    'mensaje' => 'Perfil obtenido.',
                ];
                $codigo_http = 200;
                break;
            }

            if ($ruta_interna === 'perfil' && $metodo === 'PUT') {
                $authMiddleware->requiereAutenticacion();
                $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
                $nombre = $cuerpo['nombre'] ?? null;
                $email = $cuerpo['email'] ?? null;
                $contrasena = $cuerpo['contrasena'] ?? null;

                if ($nombre === null && $email === null && $contrasena === null) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'No hay campos para actualizar.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Email invÃ¡lido.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                if ($contrasena !== null && strlen($contrasena) < 8) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'La contraseÃ±a debe tener al menos 8 caracteres.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $usuarioExistente = $usuarioDao->obtenerPorId($id_usuario);
                if ($usuarioExistente === null) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Usuario no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                if ($email !== null && $email !== $usuarioExistente->email && $usuarioDao->existeEmail($email)) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Ya existe un usuario con ese email.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                try {
                    $campos = [];
                    $params = [];
                    if ($nombre !== null) { $campos[] = 'nombre = :nombre'; $params[':nombre'] = $nombre; }
                    if ($email !== null) { $campos[] = 'email = :email'; $params[':email'] = $email; }
                    if ($contrasena !== null) { $campos[] = 'contrasena_hash = :contrasena_hash'; $params[':contrasena_hash'] = password_hash($contrasena, PASSWORD_BCRYPT); }
                    $campos[] = 'fecha_actualizacion = CURRENT_TIMESTAMP';
                    $params[':id_usuario'] = $id_usuario;
                    $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id_usuario = :id_usuario';
                    $stmt = Conexion::obtener()->prepare($sql);
                    $stmt->execute($params);

                    $respuesta = ['exito' => true, 'mensaje' => 'Perfil actualizado correctamente.', 'codigo' => 200];
                    $codigo_http = 200;
                } catch (\PDOException $e) {
                    http_response_code(500);
                    $respuesta = ['exito' => false, 'datos' => null, 'mensaje' => 'Error al actualizar el perfil.', 'codigo' => 500];
                    $codigo_http = 500;
                }
                break;
            }

            http_response_code(404);
            echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Endpoint no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
            exit;

        case $uri === '/api/areas':
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'MÃ©todo no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $respuesta = $areaController->listar();
            $codigo_http = 200;
            break;

        case str_starts_with($uri, '/api/mesas'):
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'MÃ©todo no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $ruta_interna = ltrim(substr($uri, strlen('/api/mesas')), '/');
            $query_params = $_GET ?? [];

            $respuesta = $mesaController->manejar($ruta_interna, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/reservas'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/reservas')), '/');
            $query_params = $_GET ?? [];

            // Endpoint: detalle de reserva (cliente dueÃ±o, o encargado/admin)
            if (preg_match('/^(\d+)\/detalle$/', $ruta_interna, $c) && $metodo === 'GET') {
                $authMiddleware->requiereAutenticacion();
                $id_reserva = (int) $c[1];
                $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
                $esAdmin = ServicioDeAutenticacion::esAdministrador();
                $esEncargado = ServicioDeAutenticacion::esMesero();

                $pdo = Conexion::obtener();
                $stmt = $pdo->prepare('SELECT r.*, m.numero_mesa, u.nombre AS nombre_usuario, u.email AS email_usuario FROM reservas r JOIN mesas m ON m.id_mesa = r.id_mesa JOIN usuarios u ON u.id_usuario = r.id_usuario WHERE r.id_reserva = ?');
                $stmt->execute([$id_reserva]);
                $reserva = $stmt->fetch();
                if (!$reserva) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Reserva no encontrada.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if (!($esAdmin || $esEncargado) && (int) $reserva['id_usuario'] !== $id_usuario) {
                    http_response_code(403);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'No puede ver esta reserva.', 'codigo' => 403], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $respuesta = [
                    'exito' => true,
                    'datos' => [
                        'id_reserva' => (int) $reserva['id_reserva'],
                        'id_mesa' => (int) $reserva['id_mesa'],
                        'numero_mesa' => $reserva['numero_mesa'],
                        'id_usuario' => (int) $reserva['id_usuario'],
                        'nombre_usuario' => $reserva['nombre_usuario'],
                        'email_usuario' => $reserva['email_usuario'],
                        'fecha_reserva' => $reserva['fecha_reserva'],
                        'hora_inicio' => $reserva['hora_inicio'],
                        'hora_fin' => $reserva['hora_fin'],
                        'estado' => $reserva['estado'],
                        'observaciones' => $reserva['observaciones'] ?? null,
                        'fecha_creacion' => $reserva['fecha_creacion'],
                        'fecha_actualizacion' => $reserva['fecha_actualizacion'],
                    ],
                    'mensaje' => 'Detalle de reserva obtenido.',
                ];
                $codigo_http = 200;
                break;
            }

            $respuesta = $reservaController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/pedidos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/pedidos')), '/');
            $query_params = $_GET ?? [];

            // Endpoint: detalle de pedido con Ã­tems (cliente dueÃ±o, o encargado/admin)
            if (preg_match('/^(\d+)\/detalle$/', $ruta_interna, $c) && $metodo === 'GET') {
                $authMiddleware->requiereAutenticacion();
                $id_pedido = (int) $c[1];
                $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
                $esAdmin = ServicioDeAutenticacion::esAdministrador();
                $esEncargado = ServicioDeAutenticacion::esMesero();

                $pdo = Conexion::obtener();
                // Obtener pedido
                $stmt = $pdo->prepare('SELECT p.*, m.numero_mesa FROM pedidos p LEFT JOIN mesas m ON m.id_mesa = p.id_mesa WHERE p.id_pedido = ?');
                $stmt->execute([$id_pedido]);
                $pedido = $stmt->fetch();
                if (!$pedido) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Pedido no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                // Validar propiedad o rol
                if (!($esAdmin || $esEncargado) && (int) $pedido['id_usuario'] !== $id_usuario) {
                    http_response_code(403);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'No puede ver este pedido.', 'codigo' => 403], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                // Obtener detalles
                $stmtDet = $pdo->prepare('SELECT dp.*, pr.nombre AS nombre_producto, cat.nombre AS categoria FROM detalle_pedidos dp JOIN productos pr ON pr.id_producto = dp.id_producto LEFT JOIN categorias cat ON cat.id_categoria = pr.id_categoria WHERE dp.id_pedido = ?');
                $stmtDet->execute([$id_pedido]);
                $detalles = $stmtDet->fetchAll();

                $respuesta = [
                    'exito' => true,
                    'datos' => [
                        'pedido' => $pedido,
                        'detalles' => array_map(function ($d) {
                            return [
                                'id_detalle' => (int) $d['id_detalle'],
                                'id_producto' => (int) $d['id_producto'],
                                'nombre_producto' => $d['nombre_producto'],
                                'categoria' => $d['categoria'],
                                'cantidad' => (int) $d['cantidad'],
                                'precio_unitario' => (float) $d['precio_unitario'],
                            ];
                        }, $detalles),
                    ],
                    'mensaje' => 'Detalle de pedido obtenido.',
                ];
                $codigo_http = 200;
                break;
            }

            // Endpoint: asignar mesero/responsable a un pedido (encargado/admin)
            if (preg_match('/^(\d+)\/mesero$/', $ruta_interna, $c) && $metodo === 'PUT') {
                $authMiddleware->requiereMeseroOAdministrador();
                $id_pedido = (int) $c[1];
                $cuerpo = leerCuerpoJson();
                $id_mesero = isset($cuerpo['id_mesero']) ? (int) $cuerpo['id_mesero'] : null;

                if ($id_mesero === null || $id_mesero <= 0) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Se requiere id_mesero vÃ¡lido.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $pdo = Conexion::obtener();
                // Verificar que el pedido exista
                $stmt = $pdo->prepare('SELECT id_pedido FROM pedidos WHERE id_pedido = ?');
                $stmt->execute([$id_pedido]);
                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Pedido no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                // Verificar que el mesero exista y sea encargado (rol 2) o administrador (rol 4)
                $stmt = $pdo->prepare('SELECT id_usuario, id_rol FROM usuarios WHERE id_usuario = ? AND activo = 1');
                $stmt->execute([$id_mesero]);
                $mesero = $stmt->fetch();
                if (!$mesero) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'El usuario especificado no existe o estÃ¡ inactivo.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if (!in_array((int) $mesero['id_rol'], [2, 4], true)) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'El mesero debe ser encargado o administrador.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $stmt = $pdo->prepare('UPDATE pedidos SET id_mesero = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_pedido = ?');
                $stmt->execute([$id_mesero, $id_pedido]);

                // Historial
                $stmtHist = $pdo->prepare('INSERT INTO historial_movimientos (tabla_afectada, id_registro, accion, detalles, id_usuario, direccion_ip) VALUES (:ta, :ir, :ac, :de, :iu, :ip)');
                $stmtHist->execute([
                    ':ta' => 'pedidos',
                    ':ir' => $id_pedido,
                    ':ac' => 'ASIGNAR_MESERO',
                    ':de' => json_encode(['id_pedido' => $id_pedido, 'id_mesero_asignado' => $id_mesero], JSON_UNESCAPED_UNICODE),
                    ':iu' => ServicioDeAutenticacion::idUsuarioAutenticado(),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);

                $respuesta = ['exito' => true, 'mensaje' => 'Mesero asignado correctamente.', 'codigo' => 200];
                $codigo_http = 200;
                break;
            }

            // Endpoint especÃ­fico para marcar seÃ±a como pagada
            if (preg_match('/^(\d+)\/seÃ±a$/', $ruta_interna, $c) && $metodo === 'POST') {
                $authMiddleware->requiereMeseroOAdministrador();
                $id_pedido = (int) $c[1];
                $cuerpo = leerCuerpoJson();
                $estado = $cuerpo['estado'] ?? 'pagada';

                try {
                    $stmt = $pdo->prepare('UPDATE pedidos SET estado_seÃ±a = ? WHERE id_pedido = ?');
                    $stmt->execute([$estado, $id_pedido]);
                    if ($stmt->rowCount() === 0) {
                        http_response_code(404);
                        echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Pedido no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    $respuesta = ['exito' => true, 'mensaje' => 'Estado de seÃ±a actualizado.', 'codigo' => 200];
                    $codigo_http = 200;
                    break;
                } catch (\PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Error al actualizar la seÃ±a.', 'codigo' => 500], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $respuesta = $pedidoController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Productos mÃ¡s vendidos (pÃºblico - para carta destacada)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/productos-mas-vendidos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/productos-mas-vendidos')), '/');
            if ($ruta_interna === '' || $ruta_interna === 'listar') {
                if ($metodo !== 'GET') {
                    http_response_code(405);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'MÃ©todo no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $pdo = Conexion::obtener();
                $sql = 'SELECT pr.id_producto, pr.nombre, cat.nombre AS categoria, pr.precio, pr.imagen_url,
                               SUM(dp.cantidad) AS cantidad_vendida
                        FROM detalle_pedidos dp
                        JOIN pedidos p ON p.id_pedido = dp.id_pedido
                        JOIN productos pr ON pr.id_producto = dp.id_producto
                        LEFT JOIN categorias cat ON cat.id_categoria = pr.id_categoria
                        WHERE p.estado <> :cancelado AND pr.activo = 1
                        GROUP BY pr.id_producto, pr.nombre, cat.nombre, pr.precio, pr.imagen_url
                        ORDER BY cantidad_vendida DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':cancelado' => 'cancelado']);
                $datos = $stmt->fetchAll();
                return [
                    'exito' => true,
                    'datos' => $datos,
                    'mensaje' => 'Productos mÃ¡s vendidos obtenidos.',
                    'codigo' => 200,
                ];
            }
            return errorJson('Endpoint de productos mÃ¡s vendidos no encontrado.', 404);
            break;

        // -------------------------------------------------------------------------
        // Menu destacado: 3 productos mÃ¡s vendidos (sin tabla menu_semanal)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/menu-destacado'):
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'MÃ©todo no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $pdo = Conexion::obtener();
            $sql = 'SELECT pr.id_producto, pr.nombre, cat.nombre AS categoria, pr.precio, pr.imagen_url,
                           SUM(dp.cantidad) AS cantidad_vendida
                    FROM detalle_pedidos dp
                    JOIN pedidos p ON p.id_pedido = dp.id_pedido
                    JOIN productos pr ON pr.id_producto = dp.id_producto
                    LEFT JOIN categorias cat ON cat.id_categoria = pr.id_categoria
                    WHERE p.estado <> :cancelado AND pr.activo = 1
                    GROUP BY pr.id_producto, pr.nombre, cat.nombre, pr.precio, pr.imagen_url
                    ORDER BY cantidad_vendida DESC
                    LIMIT 3';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':cancelado' => 'cancelado']);
            $respuesta = ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'MenÃº destacado obtenido.', 'codigo' => 200];
            $codigo_http = 200;
            break;

        case str_starts_with($uri, '/api/productos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/productos')), '/');
            $cuerpo = leerCuerpoJson();

            // Rutas de escritura requieren autenticaciÃ³n (encargado/administrador)
            if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true)) {
                $authMiddleware->requiereMeseroOAdministrador();
            }

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $productoController->manejar($ruta_interna, $metodo);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/usuarios'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/usuarios')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $usuarioAdminController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/areas'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/areas')), '/');
            $cuerpo = leerCuerpoJson();

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $areaAdminController->manejar($ruta_interna, $metodo);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/mesas'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/mesas')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $mesaAdminController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/stock'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/stock')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            // Endpoint: stock bajo / alerta (encargado/admin)
            if ($ruta_interna === 'bajo' && $metodo === 'GET') {
                $authMiddleware->requiereMeseroOAdministrador();
                $pdo = Conexion::obtener();
                $umbral = isset($query_params['umbral']) ? (int) $query_params['umbral'] : 5;
                $sql = 'SELECT sp.id_stock, sp.id_producto, p.nombre, cat.nombre AS categoria, sp.stock_actual, sp.limite_venta, sp.activo
                        FROM stock_producto sp
                        JOIN productos p ON p.id_producto = sp.id_producto
                        LEFT JOIN categorias cat ON cat.id_categoria = p.id_categoria
                        WHERE sp.activo = 1 AND p.activo = 1
                          AND (sp.stock_actual <= :umbral OR (sp.limite_venta > 0 AND sp.stock_actual <= sp.limite_venta))
                        ORDER BY sp.stock_actual ASC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':umbral' => $umbral]);
                $respuesta = ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Productos con stock bajo obtenidos.', 'codigo' => 200];
                $codigo_http = 200;
                break;
            }

            $respuesta = manejarStock($ruta_interna, $metodo, $query_params ?? [], $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/reportes'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/reportes')), '/');
            $query_params = $_GET ?? [];
            $respuesta = manejarReportes($ruta_interna, $metodo, $query_params, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/historial'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/historial')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarHistorial($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Subir imagen al servidor (RF-XX)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/subir-imagen'):
            if ($metodo === 'POST') {
                $authMiddleware->requiereMeseroOAdministrador();
                $respuesta = manejarSubidaImagen($cuerpo, $authMiddleware);
                $codigo_http = $respuesta['codigo'] ?? 200;
                break;
            }
            return errorJson('MÃ©todo no permitido.', 405);
            break;

        default:
            $htmlFile = __DIR__ . '/index.html';
            if (is_file($htmlFile) && !str_starts_with($uri, '/api/')) {
                http_response_code(200);
                echo file_get_contents($htmlFile);
                exit;
            }

            http_response_code(404);
            echo json_encode([
                'exito' => false,
                'datos' => null,
                'mensaje' => 'Endpoint no encontrado.',
                'codigo' => 404,
            ], JSON_UNESCAPED_UNICODE);
            exit;
    }

} catch (\RuntimeException $e) {
    $codigo_http = (int) ($e->getCode() ?: 500);
    if ($codigo_http < 400 || $codigo_http >= 600) {
        $codigo_http = 500;
    }
    Logger::error($e->getMessage(), ['codigo' => $codigo_http, 'trace' => $e->getTraceAsString()]);
    http_response_code($codigo_http);
    $respuesta = [
        'exito' => false,
        'datos' => null,
        'mensaje' => $e->getMessage(),
        'codigo' => $codigo_http,
    ];
} catch (\Throwable $e) {
    Logger::error($e->getMessage(), ['tipo' => get_class($e), 'trace' => $e->getTraceAsString()]);
    error_log('FATAL_ERROR: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    $mensajeError = Config::obtener('APP_ENV') === 'development' ? $e->getMessage() : 'Error interno del servidor.';
    $respuesta = [
        'exito' => false,
        'datos' => null,
        'mensaje' => $mensajeError,
        'codigo' => 500,
    ];
}

// -------------------------------------------------------------------------
// Stock / lÃ­mite de ventas por producto (encargado y administrador)
// -------------------------------------------------------------------------
function manejarStock(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();

    if ($ruta === '' || $ruta === 'listar') {
        if ($metodo !== 'GET') {
            return errorJson('MÃ©todo no permitido.', 405);
        }
        $sql = 'SELECT sp.id_stock, sp.id_producto, p.nombre, sp.stock_actual, sp.limite_venta, sp.activo
                FROM stock_producto sp
                JOIN productos p ON p.id_producto = sp.id_producto
                ORDER BY p.nombre';
        $stmt = $pdo->query($sql);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Stock obtenido.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $id_producto = (int) ($cuerpo['id_producto'] ?? 0);
        $stock_actual = isset($cuerpo['stock_actual']) ? (int) $cuerpo['stock_actual'] : 0;
        $limite_venta = isset($cuerpo['limite_venta']) ? (int) $cuerpo['limite_venta'] : 0;
        if ($id_producto <= 0) {
            return errorJson('id_producto requerido.', 400);
        }
        $existe = $pdo->prepare('SELECT COUNT(*) FROM stock_producto WHERE id_producto = ?');
        $existe->execute([$id_producto]);
        if ((int) $existe->fetchColumn() > 0) {
            $stmt = $pdo->prepare('UPDATE stock_producto SET stock_actual = :sa, limite_venta = :lv, activo = 1 WHERE id_producto = :ip');
            $stmt->execute([':sa' => $stock_actual, ':lv' => $limite_venta, ':ip' => $id_producto]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO stock_producto (id_producto, stock_actual, limite_venta, activo) VALUES (:ip, :sa, :lv, 1)');
            $stmt->execute([':ip' => $id_producto, ':sa' => $stock_actual, ':lv' => $limite_venta]);
        }
        return ['exito' => true, 'mensaje' => 'Stock guardado.', 'codigo' => 200];
    }

    return errorJson('Endpoint no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Disponibilidad pÃºblica (clientes ven quÃ© dÃ­as tienen mesas)
// -------------------------------------------------------------------------
function manejarReportes(string $ruta, string $metodo, array $query, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereAdministrador();
    $pdo = Conexion::obtener();
    $fechaDesde = $query['fecha_desde'] ?? '';
    $fechaHasta = $query['fecha_hasta'] ?? '';

    // 1. Ventas totales por periodo (estado != cancelado, agrupar por fecha)
    if (($ruta === '' || $ruta === 'ventas') && $metodo === 'GET') {
        $sql = 'SELECT DATE(p.fecha_pedido) AS fecha, SUM(p.total) AS total_ventas
                FROM pedidos p
                WHERE p.estado <> :cancelado';
        $params = [':cancelado' => 'cancelado'];
        if ($fechaDesde !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) >= :fd';
            $params[':fd'] = $fechaDesde;
        }
        if ($fechaHasta !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) <= :fh';
            $params[':fh'] = $fechaHasta;
        }
        $sql .= ' GROUP BY DATE(p.fecha_pedido) ORDER BY fecha';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Ventas totales por perÃ­odo obtenidas.', 'codigo' => 200];
    }

    // 2. Top productos mÃ¡s vendidos (detalle_pedidos join pedidos por fecha)
    if ($ruta === 'productos-mas-vendidos' && $metodo === 'GET') {
        $sql = 'SELECT pr.id_producto, pr.nombre, SUM(dp.cantidad) AS cantidad_vendida,
                       SUM(dp.cantidad * dp.precio_unitario) AS total_vendido
                FROM detalle_pedidos dp
                JOIN pedidos p ON p.id_pedido = dp.id_pedido
                JOIN productos pr ON pr.id_producto = dp.id_producto
                WHERE p.estado <> :cancelado';
        $params = [':cancelado' => 'cancelado'];
        if ($fechaDesde !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) >= :fd';
            $params[':fd'] = $fechaDesde;
        }
        if ($fechaHasta !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) <= :fh';
            $params[':fh'] = $fechaHasta;
        }
        $sql .= ' GROUP BY pr.id_producto, pr.nombre ORDER BY cantidad_vendida DESC LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Productos mÃ¡s vendidos obtenidos.', 'codigo' => 200];
    }

    // 3. Cantidad de reservas por dÃ­a
    if ($ruta === 'reservas-por-dia' && $metodo === 'GET') {
        $sql = 'SELECT r.fecha_reserva AS fecha, COUNT(*) AS cantidad_reservas
                FROM reservas r
                WHERE 1 = 1';
        $params = [];
        if ($fechaDesde !== '') {
            $sql .= ' AND r.fecha_reserva >= :fd';
            $params[':fd'] = $fechaDesde;
        }
        if ($fechaHasta !== '') {
            $sql .= ' AND r.fecha_reserva <= :fh';
            $params[':fh'] = $fechaHasta;
        }
        $sql .= ' GROUP BY r.fecha_reserva ORDER BY fecha';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Reservas por dÃ­a obtenidas.', 'codigo' => 200];
    }

    // 4. Ventas por dÃ­a (fecha, total, cantidad de pedidos)
    if ($ruta === 'ventas-por-dia' && $metodo === 'GET') {
        $sql = 'SELECT DATE(p.fecha_pedido) AS fecha, SUM(p.total) AS total, COUNT(*) AS cantidad_pedidos
                FROM pedidos p
                WHERE p.estado <> :cancelado';
        $params = [':cancelado' => 'cancelado'];
        if ($fechaDesde !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) >= :fd';
            $params[':fd'] = $fechaDesde;
        }
        if ($fechaHasta !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) <= :fh';
            $params[':fh'] = $fechaHasta;
        }
        $sql .= ' GROUP BY DATE(p.fecha_pedido) ORDER BY fecha';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Ventas por dÃ­a obtenidas.', 'codigo' => 200];
    }

    // 5. Historial de movimientos (vÃ­a /reportes/historial)
    if ($ruta === 'historial' && $metodo === 'GET') {
        return obtenerHistorial($query);
    }

    // 6. Exportar reporte de ventas a CSV (RF28)
    if ($ruta === 'exportar' && $metodo === 'GET') {
        $fechaDesde = $query['fecha_desde'] ?? '';
        $fechaHasta = $query['fecha_hasta'] ?? '';
        $formato = $query['formato'] ?? 'csv';

        $sql = 'SELECT DATE(p.fecha_pedido) AS fecha, p.id_pedido, p.tipo_pedido, p.estado, p.total, p.seÃ±a, u.nombre AS nombre_usuario, m.numero_mesa
                FROM pedidos p
                LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
                LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
                WHERE p.estado <> :cancelado';
        $params = [':cancelado' => 'cancelado'];
        if ($fechaDesde !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) >= :fd';
            $params[':fd'] = $fechaDesde;
        }
        if ($fechaHasta !== '') {
            $sql .= ' AND DATE(p.fecha_pedido) <= :fh';
            $params[':fh'] = $fechaHasta;
        }
        $sql .= ' ORDER BY p.fecha_pedido DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll();

        if ($formato === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="reporte_ventas.csv"');
            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['Fecha', 'Pedido', 'Tipo', 'Estado', 'Total', 'SeÃ±a', 'Usuario', 'Mesa'], ';');
            foreach ($ventas as $v) {
                fputcsv($salida, [
                    $v['fecha'],
                    $v['id_pedido'],
                    $v['tipo_pedido'],
                    $v['estado'],
                    $v['total'],
                    $v['seÃ±a'],
                    $v['nombre_usuario'] ?? '',
                    $v['numero_mesa'] ?? '',
                ], ';');
            }
            fclose($salida);
            exit;
        }

        return ['exito' => true, 'datos' => $ventas, 'mensaje' => 'Datos de exportaciÃ³n obtenidos.', 'codigo' => 200];
    }

    return errorJson('Endpoint de reporte no encontrado.', 404);
}

function obtenerHistorial(array $query): array
{
    $pdo = Conexion::obtener();
    $tabla = $query['tabla'] ?? '';
    $accion = $query['accion'] ?? '';
    $fechaDesde = $query['fecha_desde'] ?? '';
    $fechaHasta = $query['fecha_hasta'] ?? '';

    $sql = 'SELECT id_historial, tabla_afectada, id_registro, accion, detalles, id_usuario, fecha_movimiento, direccion_ip
            FROM historial_movimientos';
    $condiciones = [];
    $params = [];
    if ($tabla !== '') {
        $condiciones[] = 'tabla_afectada = :tabla';
        $params[':tabla'] = $tabla;
    }
    if ($accion !== '') {
        $condiciones[] = 'accion = :accion';
        $params[':accion'] = $accion;
    }
    if ($fechaDesde !== '') {
        $condiciones[] = 'DATE(fecha_movimiento) >= :fd';
        $params[':fd'] = $fechaDesde;
    }
    if ($fechaHasta !== '') {
        $condiciones[] = 'DATE(fecha_movimiento) <= :fh';
        $params[':fh'] = $fechaHasta;
    }
    if ($condiciones !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $condiciones);
    }
    $sql .= ' ORDER BY fecha_movimiento DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Historial de movimientos obtenido.', 'codigo' => 200];
}

// -------------------------------------------------------------------------
// Historial de movimientos (GET: administrador, POST: uso interno del sistema)
// -------------------------------------------------------------------------
function manejarHistorial(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $pdo = Conexion::obtener();

    // GET: listar historial con filtros opcionales (solo administrador)
    if ($ruta === '' && $metodo === 'GET') {
        $authMiddleware->requiereAdministrador();
        return obtenerHistorial($query);
    }

    // POST: insertar un movimiento (uso interno del sistema, sin auth de admin)
    if ($ruta === '' && $metodo === 'POST') {
        $tabla = $cuerpo['tabla_afectada'] ?? $cuerpo['tabla'] ?? '';
        $accion = $cuerpo['accion'] ?? '';
        if ($tabla === '' || $accion === '') {
            return errorJson('Se requieren tabla_afectada y accion.', 400);
        }
        $idRegistro = isset($cuerpo['id_registro']) ? (int) $cuerpo['id_registro'] : null;
        $detalles = isset($cuerpo['detalles']) ? json_encode($cuerpo['detalles'], JSON_UNESCAPED_UNICODE) : null;
        $idUsuario = isset($cuerpo['id_usuario']) ? (int) $cuerpo['id_usuario'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            $stmt = $pdo->prepare('
                INSERT INTO historial_movimientos (tabla_afectada, id_registro, accion, detalles, id_usuario, direccion_ip)
                VALUES (:t, :ir, :a, :d, :u, :ip)
            ');
            $stmt->execute([
                ':t' => $tabla,
                ':ir' => $idRegistro,
                ':a' => $accion,
                ':d' => $detalles,
                ':u' => $idUsuario,
                ':ip' => $ip,
            ]);
            return ['exito' => true, 'datos' => ['id_historial' => (int) $pdo->lastInsertId()], 'mensaje' => 'Movimiento insertado en el historial.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al insertar movimiento en el historial.', 500);
        }
    }

    return errorJson('Endpoint de historial no encontrado.', 404);
}

// -------------------------------------------------------------------------
// ConfiguraciÃ³n general del sistema (RF-101)
// -------------------------------------------------------------------------
function manejarSubidaImagen(array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereMeseroOAdministrador();

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        return errorJson('No se recibiÃ³ ningÃºn archivo de imagen.', 400);
    }

    $archivo = $_FILES['imagen'];
    $nombreArchivo = basename($archivo['name']);
    $nombreArchivo = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreArchivo);
    $nombreUnico = uniqid('img_', true) . '_' . $nombreArchivo;

    $directorioDestino = __DIR__ . '/uploads/';
    if (!is_dir($directorioDestino)) {
        @mkdir($directorioDestino, 0755, true);
    }

    $rutaDestino = $directorioDestino . $nombreUnico;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        return errorJson('No se pudo guardar el archivo en el servidor.', 500);
    }

    $urlImagen = Config::obtener('APP_URL') . '/uploads/' . $nombreUnico;

    return [
        'exito' => true,
        'url' => $urlImagen,
        'mensaje' => 'Imagen subida correctamente.',
        'codigo' => 200,
    ];
}

// -------------------------------------------------------------------------
// RF-84: Estado de entrega de delivery
// -------------------------------------------------------------------------
function aplicarMigraciones(PDO $pdo): void
{
    $migrationsDir = __DIR__ . '/../migrations';
    if (!is_dir($migrationsDir)) {
        return;
    }
    $archivos = scandir($migrationsDir);
    sort($archivos);
    foreach ($archivos as $archivo) {
        if (str_ends_with($archivo, '.sql')) {
            $ruta = $migrationsDir . '/' . $archivo;
            $contenido = file_get_contents($ruta);
            if ($contenido === false) {
                continue;
            }
            // Dividir por punto y coma, ignorar comentarios y vacÃ­os
            $statements = array_filter(array_map('trim', explode(';', $contenido)), function ($line) {
                $line = trim($line);
                return $line !== '' && !str_starts_with($line, '--') && !str_starts_with($line, '#');
            });
            foreach ($statements as $stmt) {
                try {
                    $pdo->exec($stmt);
                } catch (\PDOException $e) {
                    // Ignorar errores de duplicado (tabla/columna ya existe)
                }
            }
        }
    }
}

function errorJson(string $mensaje, int $codigo): array
{
    http_response_code($codigo);
    return ['exito' => false, 'datos' => null, 'mensaje' => $mensaje, 'codigo' => $codigo];
}

http_response_code($codigo_http);
echo json_encode($respuesta ?? ['exito' => false, 'mensaje' => 'Respuesta vacÃ­a.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
