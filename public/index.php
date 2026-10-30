<?php

declare(strict_types=1);

namespace Restaurante;

use Restaurante\Config\Config;
use Restaurante\Database\Conexion;
use Restaurante\Dao\AreaDao;
use Restaurante\Dao\BloqueMesaDao;
use Restaurante\Dao\DetallePedidoDao;
use Restaurante\Dao\HorarioFuncionamientoDao;
use Restaurante\Dao\IntentosLoginDao;
use Restaurante\Dao\MesaDao;
use Restaurante\Dao\PedidoDao;
use Restaurante\Dao\ProductoDao;
use Restaurante\Dao\RecuperarContrasenaDao;
use Restaurante\Dao\ReservaDao;
use Restaurante\Dao\UsuarioDao;
use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Middleware\RateLimiterMiddleware;
use Restaurante\Service\ServicioDeAutenticacion;
use Restaurante\Service\ServicioDeBloques;
use Restaurante\Service\ServicioDeDisponibilidad;
use Restaurante\Service\ServicioDeHorarios;
use Restaurante\Service\ServicioDeMesas;
use Restaurante\Service\ServicioDePedidos;
use Restaurante\Service\ServicioDeProductos;
use Restaurante\Service\ServicioDeReservas;
use Restaurante\Validator\ValidadorDeReserva;
use Restaurante\Controller\AreaAdminController;
use Restaurante\Controller\AreaController;
use Restaurante\Controller\AuthController;
use Restaurante\Controller\BloqueAdminController;
use Restaurante\Controller\HorarioAdminController;
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
if (is_file($envFile)) {
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
            if (!isset($_ENV[$clave])) {
                $_ENV[$clave] = $valor;
                putenv(sprintf('%s=%s', $clave, $valor));
            }
        }
    }
}

ConfiguracionSesion::aplicar();

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
$horarioDao = new HorarioFuncionamientoDao();
$bloqueDao = new BloqueMesaDao();
$recuperarDao = new RecuperarContrasenaDao();
$auth = new ServicioDeAutenticacion($usuarioDao, $intentosDao, $recuperarDao);
$servicioDeMesas = new ServicioDeMesas($areaDao, $mesaDao);
$servicioDeDisponibilidad = new ServicioDeDisponibilidad();
$validadorDeReserva = new ValidadorDeReserva($reservaDao, $mesaDao);
$servicioDeReservas = new ServicioDeReservas($reservaDao, $validadorDeReserva);
$servicioDePedidos = new ServicioDePedidos($pedidoDao, $productoDao, $mesaDao);
$servicioDeProductos = new ServicioDeProductos($productoDao);
$servicioDeHorarios = new ServicioDeHorarios($horarioDao);
$servicioDeBloques = new ServicioDeBloques($bloqueDao);
$authMiddleware = new AuthMiddleware($auth);
$rateLimiter = new RateLimiterMiddleware();

$authController = new AuthController($auth, $authMiddleware, $rateLimiter);
$areaController = new AreaController($servicioDeMesas);
$mesaController = new MesaController($servicioDeMesas, $servicioDeDisponibilidad);
$reservaController = new ReservaController($servicioDeReservas, $authMiddleware);
$pedidoController = new PedidoController($servicioDePedidos, $authMiddleware);
$productoController = new ProductoController($servicioDeProductos, $authMiddleware);
$usuarioAdminController = new UsuarioAdminController($usuarioDao, $authMiddleware, $auth);
$areaAdminController = new AreaAdminController($areaDao, $authMiddleware);
$mesaAdminController = new MesaAdminController($mesaDao, $authMiddleware);
$horarioAdminController = new HorarioAdminController($servicioDeHorarios, $authMiddleware);
$bloqueAdminController = new BloqueAdminController($servicioDeBloques, $authMiddleware);

function leerCuerpoJson(): array
{
    $cuerpo = file_get_contents('php://input');
    $datos = json_decode($cuerpo, true);

    return is_array($datos) ? $datos : [];
}

$respuesta = null;
$codigo_http = 200;

try {
    $pdo = Conexion::obtener();
    $pdo->query('SELECT 1');

    switch (true) {
        case $uri === '/':
            $respuesta = [
                'exito' => true,
                'aplicacion' => 'Sistema de gestión de un restaurante',
                'version' => '1.0.0-completo',
                'entorno' => Config::obtener('APP_ENV'),
                'base_de_datos' => 'conectada',
                'ruta' => $uri,
                'metodo' => $metodo,
                'mensaje' => 'Sistema completo: autenticación, mesas, reservas, pedidos, productos, administración.',
            ];
            break;

        case str_starts_with($uri, '/api/auth'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/auth')), '/');

            if (in_array($ruta_interna, ['login', 'registro', 'recuperar', 'restablecer'], true) && $metodo === 'POST') {
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
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Email inválido.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                if ($contrasena !== null && strlen($contrasena) < 8) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
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
                echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Método no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $respuesta = $areaController->listar();
            $codigo_http = 200;
            break;

        case str_starts_with($uri, '/api/mesas'):
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Método no permitido.', 'codigo' => 405], JSON_UNESCAPED_UNICODE);
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

            // Endpoint: detalle de reserva (cliente dueño, o encargado/admin)
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

            // Endpoint: detalle de pedido con ítems (cliente dueño, o encargado/admin)
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
                $stmtDet = $pdo->prepare('SELECT dp.*, pr.nombre AS nombre_producto, pr.categoria FROM detalle_pedidos dp JOIN productos pr ON pr.id_producto = dp.id_producto WHERE dp.id_pedido = ?');
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
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Se requiere id_mesero válido.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
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
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'El usuario especificado no existe o está inactivo.', 'codigo' => 400], JSON_UNESCAPED_UNICODE);
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

            // Endpoint específico para marcar seña como pagada
            if (preg_match('/^(\d+)\/seña$/', $ruta_interna, $c) && $metodo === 'POST') {
                $authMiddleware->requiereMeseroOAdministrador();
                $id_pedido = (int) $c[1];
                $cuerpo = leerCuerpoJson();
                $estado = $cuerpo['estado'] ?? 'pagada';

                try {
                    $stmt = $pdo->prepare('UPDATE pedidos SET estado_seña = ? WHERE id_pedido = ?');
                    $stmt->execute([$estado, $id_pedido]);
                    if ($stmt->rowCount() === 0) {
                        http_response_code(404);
                        echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Pedido no encontrado.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    $respuesta = ['exito' => true, 'mensaje' => 'Estado de seña actualizado.', 'codigo' => 200];
                    $codigo_http = 200;
                    break;
                } catch (\PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['exito' => false, 'datos' => null, 'mensaje' => 'Error al actualizar la seña.', 'codigo' => 500], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $respuesta = $pedidoController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/productos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/productos')), '/');
            $cuerpo = leerCuerpoJson();

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

        case str_starts_with($uri, '/api/admin/horarios'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/horarios')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $horarioAdminController->manejar($ruta_interna, $metodo, $query_params);
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
                $sql = 'SELECT sp.id_stock, sp.id_producto, p.nombre, p.categoria, sp.stock_actual, sp.limite_venta, sp.activo
                        FROM stock_producto sp
                        JOIN productos p ON p.id_producto = sp.id_producto
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

        case str_starts_with($uri, '/api/disponibilidad'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/disponibilidad')), '/');
            $query_params = $_GET ?? [];
            $respuesta = manejarDisponibilidadPublica($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/disponibilidad'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/disponibilidad')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }
            $respuesta = manejarDisponibilidadAdmin($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/admin/bloques'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/bloques')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();

            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }

            $respuesta = $bloqueAdminController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/menu-semanal'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/menu-semanal')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
            }
            $respuesta = manejarMenuSemanal($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
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
        // Configuración general del sistema (RF-101)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/configuracion'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/configuracion')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarConfiguracionSistema($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Límite de pedidos sin reserva (RF-68, RF-69, RF-70, RF-71)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/pedidos-sin-reserva'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/pedidos-sin-reserva')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarLimitePedidosSinReserva($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Zonas de delivery (RF-81)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/zonas-delivery'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/zonas-delivery')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarZonasDelivery($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Repartidores (RF-83)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/repartidores'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/repartidores')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarRepartidores($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Cupones y descuentos (RF-88)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/cupones'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/cupones')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarCupones($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Calificaciones de platos (RF-91)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/calificaciones'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/calificaciones')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarCalificaciones($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Preferencias y alergias del cliente (RF-92)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/preferencias'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/preferencias')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarPreferencias($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // Puntos de fidelidad (RF-90)
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/puntos-fidelidad'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/puntos-fidelidad')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarPuntosFidelidad($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-84: Estado de entrega de delivery
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/pedidos/{id}/entrega'):
        case str_starts_with($uri, '/api/pedidos/{id}/entrega'):
            $ruta_interna = ltrim(substr($uri, strrpos($uri, '/api/pedidos')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarEstadoEntrega($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-87: Cierre de caja diario
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/cierre-caja'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/cierre-caja')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarCierreCaja($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-89: Platos favoritos del cliente
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/favoritos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/favoritos')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarFavoritos($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-93: Reservas recurrentes
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/reservas-recurrentes'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/reservas-recurrentes')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarReservasRecurrentes($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-94 a RF-96: Notificaciones
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/notificaciones'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/notificaciones')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarNotificaciones($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware, $auth);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-97 a RF-100: Reportes avanzados
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/reportes-avanzados'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/reportes-avanzados')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarReportesAvanzados($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-102: Sucursales múltiples
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/sucursales'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/sucursales')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarSucursales($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-103: Respaldos de base de datos
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/respaldos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/respaldos')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarRespaldos($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        // -------------------------------------------------------------------------
        // RF-105: Roles y permisos avanzados
        // -------------------------------------------------------------------------
        case str_starts_with($uri, '/api/admin/permisos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/admin/permisos')), '/');
            $query_params = $_GET ?? [];
            $cuerpo = leerCuerpoJson();
            $respuesta = manejarPermisos($ruta_interna, $metodo, $query_params, $cuerpo, $authMiddleware);
            $codigo_http = $respuesta['codigo'] ?? 200;
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
    $codigo_http = $e->getCode() ?: 500;
    if ($codigo_http < 400 || $codigo_http >= 600) {
        $codigo_http = 500;
    }
    http_response_code($codigo_http);
    $respuesta = [
        'exito' => false,
        'datos' => null,
        'mensaje' => $e->getMessage(),
        'codigo' => $codigo_http,
    ];
} catch (\Throwable $e) {
    http_response_code(500);
    $respuesta = [
        'exito' => false,
        'datos' => null,
        'mensaje' => 'Error interno del servidor.',
        'codigo' => 500,
    ];
}

// -------------------------------------------------------------------------
// Stock / límite de ventas por producto (encargado y administrador)
// -------------------------------------------------------------------------
function manejarStock(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();

    if ($ruta === '' || $ruta === 'listar') {
        if ($metodo !== 'GET') {
            return errorJson('Método no permitido.', 405);
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
// Disponibilidad pública (clientes ven qué días tienen mesas)
// -------------------------------------------------------------------------
function manejarDisponibilidadPublica(string $ruta, string $metodo, array $query): array
{
    if ($metodo !== 'GET') {
        return errorJson('Método no permitido.', 405);
    }
    $pdo = Conexion::obtener();
    $fecha = isset($query['fecha']) ? $query['fecha'] : '';
    if ($fecha === '') {
        return errorJson('Se requiere fecha.', 400);
    }
    $sql = 'SELECT id_disponibilidad, fecha, hora_inicio, hora_fin, mesas_disponibles
            FROM disponibilidad_mesa
            WHERE fecha = ? AND activo = 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fecha]);
    return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Disponibilidad obtenida.', 'codigo' => 200];
}

// -------------------------------------------------------------------------
// Disponibilidad administración (encargado abre disponibilidad por fechas)
// -------------------------------------------------------------------------
function manejarDisponibilidadAdmin(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();
    $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

    if ($ruta === '' || $ruta === 'listar') {
        if ($metodo !== 'GET') {
            return errorJson('Método no permitido.', 405);
        }
        $fecha = isset($query['fecha']) ? $query['fecha'] : null;
        if ($fecha) {
            $sql = 'SELECT * FROM disponibilidad_mesa WHERE fecha = ? ORDER BY hora_inicio';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha]);
        } else {
            $stmt = $pdo->query('SELECT * FROM disponibilidad_mesa WHERE activo = 1 ORDER BY fecha, hora_inicio');
        }
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Disponibilidad obtenida.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $fecha = $cuerpo['fecha'] ?? '';
        $hora_inicio = $cuerpo['hora_inicio'] ?? '';
        $hora_fin = $cuerpo['hora_fin'] ?? '';
        $mesas = isset($cuerpo['mesas_disponibles']) ? (int) $cuerpo['mesas_disponibles'] : 1;
        if ($fecha === '' || $hora_inicio === '' || $hora_fin === '') {
            return errorJson('Faltan fecha, hora_inicio o hora_fin.', 400);
        }
        try {
            $sql = 'INSERT INTO disponibilidad_mesa (fecha, hora_inicio, hora_fin, mesas_disponibles, activo, creado_por)
                    VALUES (:f, :hi, :hf, :m, 1, :u)
                    ON DUPLICATE KEY UPDATE hora_fin = VALUES(hora_fin), mesas_disponibles = VALUES(mesas_disponibles), activo = 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':f' => $fecha, ':hi' => $hora_inicio, ':hf' => $hora_fin, ':m' => $mesas, ':u' => $id_usuario]);
            return ['exito' => true, 'mensaje' => 'Disponibilidad guardada.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al guardar disponibilidad.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id = (int) $c[1];
        $stmt = $pdo->prepare('UPDATE disponibilidad_mesa SET activo = 0 WHERE id_disponibilidad = ?');
        $stmt->execute([$id]);
        return ['exito' => true, 'mensaje' => 'Disponibilidad desactivada.', 'codigo' => 200];
    }

    return errorJson('Endpoint no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Menú semanal (público ve, encargado/admin publica)
// -------------------------------------------------------------------------
function manejarMenuSemanal(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $pdo = Conexion::obtener();

    // GET público: listar menú semanal activo o por fecha
    if ($ruta === '' || $ruta === 'listar') {
        if ($metodo !== 'GET') {
            return errorJson('Método no permitido.', 405);
        }
        $semana = isset($query['semana']) ? $query['semana'] : null; // formato YYYY-Www o fecha lunes
        if ($semana) {
            $sql = 'SELECT ms.*, u.nombre as creado_por_nombre
                    FROM menu_semanal ms
                    LEFT JOIN usuarios u ON u.id_usuario = ms.creado_por
                    WHERE ms.semana_inicio = ? AND ms.activo = 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$semana]);
        } else {
            $sql = 'SELECT ms.*, u.nombre as creado_por_nombre
                    FROM menu_semanal ms
                    LEFT JOIN usuarios u ON u.id_usuario = ms.creado_por
                    WHERE ms.activo = 1
                    ORDER BY ms.semana_inicio DESC';
            $stmt = $pdo->query($sql);
        }
        $semanas = $stmt->fetchAll();
        $resultado = [];
        foreach ($semanas as $s) {
            $id_menu = (int) $s['id_menu_semanal'];
            $stmt2 = $pdo->prepare('
                SELECT msi.id_item, msi.id_producto, p.nombre, p.categoria, p.precio, msi.disponible, msi.precio_semanal
                FROM menu_semanal_items msi
                JOIN productos p ON p.id_producto = msi.id_producto
                WHERE msi.id_menu_semanal = ?
                ORDER BY p.categoria, p.nombre
            ');
            $stmt2->execute([$id_menu]);
            $items = $stmt2->fetchAll();
            $resultado[] = [
                'id_menu_semanal' => (int) $s['id_menu_semanal'],
                'semana_inicio' => $s['semana_inicio'],
                'semana_fin' => $s['semana_fin'],
                'activo' => (bool) $s['activo'],
                'creado_por' => $s['creado_por_nombre'] ?? null,
                'fecha_creacion' => $s['fecha_creacion'],
                'items' => array_map(function ($it) {
                    return [
                        'id_item' => (int) $it['id_item'],
                        'id_producto' => (int) $it['id_producto'],
                        'nombre' => $it['nombre'],
                        'categoria' => $it['categoria'],
                        'precio' => (float) $it['precio'],
                        'disponible' => (bool) $it['disponible'],
                        'precio_semanal' => $it['precio_semanal'] ? (float) $it['precio_semanal'] : null,
                    ];
                }, $items),
            ];
        }
        return ['exito' => true, 'datos' => $resultado, 'mensaje' => 'Menú semanal obtenido.', 'codigo' => 200];
    }

    // POST/PUT/DELETE: solo encargado o administrador
    $authMiddleware->requiereMeseroOAdministrador();
    $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

    if (($ruta === '' || $ruta === 'listar') && $metodo === 'POST') {
        $semana_inicio = $cuerpo['semana_inicio'] ?? '';
        $semana_fin = $cuerpo['semana_fin'] ?? '';
        $items = $cuerpo['items'] ?? []; // array de [id_producto, disponible, precio_semanal]
        if ($semana_inicio === '' || $semana_fin === '') {
            return errorJson('Se requieren semana_inicio y semana_fin.', 400);
        }
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('
                INSERT INTO menu_semanal (semana_inicio, semana_fin, activo, creado_por, fecha_creacion)
                VALUES (:si, :sf, 1, :u, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE semana_fin = VALUES(semana_fin), activo = 1, fecha_creacion = CURRENT_TIMESTAMP
            ');
            $stmt->execute([':si' => $semana_inicio, ':sf' => $semana_fin, ':u' => $id_usuario]);
            $id_menu = (int) $pdo->lastInsertId();

            // Si ya existía, borrar items previos y reinsertar
            $pdo->prepare('DELETE FROM menu_semanal_items WHERE id_menu_semanal = ?')->execute([$id_menu]);

            foreach ($items as $it) {
                $id_producto = (int) ($it['id_producto'] ?? 0);
                $disponible = isset($it['disponible']) ? (bool) $it['disponible'] : true;
                $precio_semanal = isset($it['precio_semanal']) && $it['precio_semanal'] !== '' ? (float) $it['precio_semanal'] : null;
                if ($id_producto <= 0) continue;
                $stmt = $pdo->prepare('
                    INSERT INTO menu_semanal_items (id_menu_semanal, id_producto, disponible, precio_semanal)
                    VALUES (:im, :ip, :d, :ps)
                ');
                $stmt->execute([':im' => $id_menu, ':ip' => $id_producto, ':d' => $disponible ? 1 : 0, ':ps' => $precio_semanal]);
            }
            $pdo->commit();
            return ['exito' => true, 'mensaje' => 'Menú semanal guardado.', 'codigo' => 200];
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return errorJson('Error al guardar el menú semanal.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id = (int) $c[1];
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM menu_semanal_items WHERE id_menu_semanal = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM menu_semanal WHERE id_menu_semanal = ?')->execute([$id]);
            $pdo->commit();
            return ['exito' => true, 'mensaje' => 'Menú semanal eliminado.', 'codigo' => 200];
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return errorJson('Error al eliminar el menú semanal.', 500);
        }
    }

    return errorJson('Endpoint no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Reportes de administración (solo administrador)
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
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Ventas totales por período obtenidas.', 'codigo' => 200];
    }

    // 2. Top productos más vendidos (detalle_pedidos join pedidos por fecha)
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
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Productos más vendidos obtenidos.', 'codigo' => 200];
    }

    // 3. Cantidad de reservas por día
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
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Reservas por día obtenidas.', 'codigo' => 200];
    }

    // 4. Ventas por día (fecha, total, cantidad de pedidos)
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
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Ventas por día obtenidas.', 'codigo' => 200];
    }

    // 5. Historial de movimientos (vía /reportes/historial)
    if ($ruta === 'historial' && $metodo === 'GET') {
        return obtenerHistorial($query);
    }

    // 6. Exportar reporte de ventas a CSV (RF28)
    if ($ruta === 'exportar' && $metodo === 'GET') {
        $fechaDesde = $query['fecha_desde'] ?? '';
        $fechaHasta = $query['fecha_hasta'] ?? '';
        $formato = $query['formato'] ?? 'csv';

        $sql = 'SELECT DATE(p.fecha_pedido) AS fecha, p.id_pedido, p.tipo_pedido, p.estado, p.total, p.seña, u.nombre AS nombre_usuario, m.numero_mesa
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
            fputcsv($salida, ['Fecha', 'Pedido', 'Tipo', 'Estado', 'Total', 'Seña', 'Usuario', 'Mesa'], ';');
            foreach ($ventas as $v) {
                fputcsv($salida, [
                    $v['fecha'],
                    $v['id_pedido'],
                    $v['tipo_pedido'],
                    $v['estado'],
                    $v['total'],
                    $v['seña'],
                    $v['nombre_usuario'] ?? '',
                    $v['numero_mesa'] ?? '',
                ], ';');
            }
            fclose($salida);
            exit;
        }

        return ['exito' => true, 'datos' => $ventas, 'mensaje' => 'Datos de exportación obtenidos.', 'codigo' => 200];
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
// Configuración general del sistema (RF-101)
// -------------------------------------------------------------------------
function manejarConfiguracionSistema(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $authMiddleware->requiereAdministrador();
    $pdo = Conexion::obtener();
    $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();

    if ($ruta === '' && $metodo === 'GET') {
        $stmt = $pdo->query('SELECT clave, valor, descripcion, fecha_actualizacion FROM configuracion_sistema ORDER BY clave');
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Configuración obtenida.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'PUT') {
        $clave = $cuerpo['clave'] ?? '';
        $valor = $cuerpo['valor'] ?? null;
        $descripcion = $cuerpo['descripcion'] ?? null;
        if ($clave === '') {
            return errorJson('Se requiere la clave de configuración.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO configuracion_sistema (clave, valor, descripcion, actualizado_por)
                VALUES (:c, :v, :d, :u)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), descripcion = VALUES(descripcion), actualizado_por = VALUES(actualizado_por), fecha_actualizacion = CURRENT_TIMESTAMP
            ');
            $stmt->execute([
                ':c' => $clave,
                ':v' => $valor !== null ? $valor : null,
                ':d' => $descripcion,
                ':u' => $id_usuario,
            ]);
            return ['exito' => true, 'mensaje' => 'Configuración guardada.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al guardar la configuración.', 500);
        }
    }

    return errorJson('Endpoint de configuración no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Límite de pedidos sin reserva (RF-68, RF-69, RF-70, RF-71)
// -------------------------------------------------------------------------
function manejarLimitePedidosSinReserva(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();

    // GET: consultar límite y contador del día
    if ($ruta === '' && $metodo === 'GET') {
        $fecha = $query['fecha'] ?? date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT clave, valor FROM configuracion_sistema
            WHERE clave IN ('limite_pedidos_sin_reserva_retiro', 'limite_pedidos_sin_reserva_domicilio')
        ");
        $stmt->execute();
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['clave']] = (int) $row['valor'];
        }

        $contadorRetiro = 0;
        $contadorDomicilio = 0;
        $stmt = $pdo->prepare('SELECT tipo_pedido, cantidad_pedidos FROM contador_pedidos_sin_reserva WHERE fecha = ?');
        $stmt->execute([$fecha]);
        while ($row = $stmt->fetch()) {
            if ($row['tipo_pedido'] === 'para_llevar') {
                $contadorRetiro = (int) $row['cantidad_pedidos'];
            } elseif ($row['tipo_pedido'] === 'domicilio') {
                $contadorDomicilio = (int) $row['cantidad_pedidos'];
            }
        }

        return [
            'exito' => true,
            'datos' => [
                'fecha' => $fecha,
                'limite_retiro' => $config['limite_pedidos_sin_reserva_retiro'] ?? 0,
                'contador_retiro' => $contadorRetiro,
                'disponible_retiro' => max(0, ($config['limite_pedidos_sin_reserva_retiro'] ?? 0) - $contadorRetiro),
                'limite_domicilio' => $config['limite_pedidos_sin_reserva_domicilio'] ?? 0,
                'contador_domicilio' => $contadorDomicilio,
                'disponible_domicilio' => max(0, ($config['limite_pedidos_sin_reserva_domicilio'] ?? 0) - $contadorDomicilio),
                'bloqueado_retiro' => $contadorRetiro >= ($config['limite_pedidos_sin_reserva_retiro'] ?? 0),
                'bloqueado_domicilio' => $contadorDomicilio >= ($config['limite_pedidos_sin_reserva_domicilio'] ?? 0),
            ],
            'mensaje' => 'Límites y contadores de pedidos sin reserva obtenidos.',
            'codigo' => 200,
        ];
    }

    // POST: establecer/actualizar límite
    if ($ruta === '' && $metodo === 'POST') {
        $tipo = $cuerpo['tipo'] ?? ''; // 'retiro' o 'domicilio'
        $limite = isset($cuerpo['limite']) ? (int) $cuerpo['limite'] : 0;
        if (!in_array($tipo, ['retiro', 'domicilio'], true)) {
            return errorJson('Tipo inválido. Use "retiro" o "domicilio".', 400);
        }
        $clave = $tipo === 'retiro' ? 'limite_pedidos_sin_reserva_retiro' : 'limite_pedidos_sin_reserva_domicilio';
        try {
            $stmt = $pdo->prepare('
                INSERT INTO configuracion_sistema (clave, valor, descripcion)
                VALUES (:c, :v, :d)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), fecha_actualizacion = CURRENT_TIMESTAMP
            ');
            $stmt->execute([
                ':c' => $clave,
                ':v' => (string) $limite,
                ':d' => 'Límite máximo de pedidos sin reserva para ' . $tipo,
            ]);
            return ['exito' => true, 'mensaje' => 'Límite actualizado.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al actualizar el límite.', 500);
        }
    }

    return errorJson('Endpoint de límite de pedidos sin reserva no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Zonas de delivery (RF-81)
// -------------------------------------------------------------------------
function manejarZonasDelivery(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();

    if (($ruta === '' || $ruta === 'listar') && $metodo === 'GET') {
        $stmt = $pdo->query('SELECT * FROM zonas_delivery ORDER BY nombre');
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Zonas de delivery obtenidas.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $nombre = $cuerpo['nombre'] ?? '';
        $costo_envio = isset($cuerpo['costo_envio']) ? (float) $cuerpo['costo_envio'] : 0.00;
        $tiempo = isset($cuerpo['tiempo_estimado_minutos']) ? (int) $cuerpo['tiempo_estimado_minutos'] : 45;
        $cobertura = $cuerpo['cobertura'] ?? null;
        if ($nombre === '') {
            return errorJson('Se requiere el nombre de la zona.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO zonas_delivery (nombre, costo_envio, tiempo_estimado_minutos, cobertura, activa)
                VALUES (:n, :ce, :t, :c, 1)
            ');
            $stmt->execute([
                ':n' => $nombre,
                ':ce' => $costo_envio,
                ':t' => $tiempo,
                ':c' => $cobertura,
            ]);
            return ['exito' => true, 'mensaje' => 'Zona de delivery creada.', 'codigo' => 200, 'datos' => ['id_zona' => (int) $pdo->lastInsertId()]];
        } catch (\PDOException $e) {
            return errorJson('Error al crear la zona de delivery.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'PUT') {
        $id_zona = (int) $c[1];
        $nombre = $cuerpo['nombre'] ?? null;
        $costo_envio = isset($cuerpo['costo_envio']) ? (float) $cuerpo['costo_envio'] : null;
        $tiempo = isset($cuerpo['tiempo_estimado_minutos']) ? (int) $cuerpo['tiempo_estimado_minutos'] : null;
        $cobertura = isset($cuerpo['cobertura']) ? $cuerpo['cobertura'] : null;
        $activa = isset($cuerpo['activa']) ? (int) $cuerpo['activa'] : null;

        $campos = [];
        $params = [];
        if ($nombre !== null) { $campos[] = 'nombre = :n'; $params[':n'] = $nombre; }
        if ($costo_envio !== null) { $campos[] = 'costo_envio = :ce'; $params[':ce'] = $costo_envio; }
        if ($tiempo !== null) { $campos[] = 'tiempo_estimado_minutos = :t'; $params[':t'] = $tiempo; }
        if ($cobertura !== null) { $campos[] = 'cobertura = :c'; $params[':c'] = $cobertura; }
        if ($activa !== null) { $campos[] = 'activa = :a'; $params[':a'] = $activa; }
        if ($campos === []) {
            return errorJson('No hay campos para actualizar.', 400);
        }
        $params[':id'] = $id_zona;
        try {
            $stmt = $pdo->prepare('UPDATE zonas_delivery SET ' . implode(', ', $campos) . ' WHERE id_zona = :id');
            $stmt->execute($params);
            return ['exito' => true, 'mensaje' => 'Zona de delivery actualizada.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al actualizar la zona.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id_zona = (int) $c[1];
        $stmt = $pdo->prepare('UPDATE zonas_delivery SET activa = 0 WHERE id_zona = ?');
        $stmt->execute([$id_zona]);
        return ['exito' => true, 'mensaje' => 'Zona de delivery desactivada.', 'codigo' => 200];
    }

    return errorJson('Endpoint de zonas de delivery no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Repartidores (RF-83)
// -------------------------------------------------------------------------
function manejarRepartidores(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();

    if (($ruta === '' || $ruta === 'listar') && $metodo === 'GET') {
        $stmt = $pdo->query('SELECT r.*, u.nombre AS nombre_usuario FROM repartidores r LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario ORDER BY r.nombre');
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Repartidores obtenidos.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $nombre = $cuerpo['nombre'] ?? '';
        $telefono = $cuerpo['telefono'] ?? null;
        $id_usuario = isset($cuerpo['id_usuario']) ? (int) $cuerpo['id_usuario'] : null;
        if ($nombre === '') {
            return errorJson('Se requiere el nombre del repartidor.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO repartidores (id_usuario, nombre, telefono, activo)
                VALUES (:u, :n, :t, 1)
            ');
            $stmt->execute([
                ':u' => $id_usuario > 0 ? $id_usuario : null,
                ':n' => $nombre,
                ':t' => $telefono,
            ]);
            return ['exito' => true, 'mensaje' => 'Repartidor creado.', 'codigo' => 200, 'datos' => ['id_repartidor' => (int) $pdo->lastInsertId()]];
        } catch (\PDOException $e) {
            return errorJson('Error al crear el repartidor.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'PUT') {
        $id_repartidor = (int) $c[1];
        $nombre = $cuerpo['nombre'] ?? null;
        $telefono = isset($cuerpo['telefono']) ? $cuerpo['telefono'] : null;
        $activa = isset($cuerpo['activo']) ? (int) $cuerpo['activo'] : null;

        $campos = [];
        $params = [];
        if ($nombre !== null) { $campos[] = 'nombre = :n'; $params[':n'] = $nombre; }
        if ($telefono !== null) { $campos[] = 'telefono = :t'; $params[':t'] = $telefono; }
        if ($activa !== null) { $campos[] = 'activo = :a'; $params[':a'] = $activa; }
        if ($campos === []) {
            return errorJson('No hay campos para actualizar.', 400);
        }
        $params[':id'] = $id_repartidor;
        try {
            $stmt = $pdo->prepare('UPDATE repartidores SET ' . implode(', ', $campos) . ' WHERE id_repartidor = :id');
            $stmt->execute($params);
            return ['exito' => true, 'mensaje' => 'Repartidor actualizado.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al actualizar el repartidor.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id_repartidor = (int) $c[1];
        $stmt = $pdo->prepare('UPDATE repartidores SET activo = 0 WHERE id_repartidor = ?');
        $stmt->execute([$id_repartidor]);
        return ['exito' => true, 'mensaje' => 'Repartidor desactivado.', 'codigo' => 200];
    }

    return errorJson('Endpoint de repartidores no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Cupones y descuentos (RF-88)
// -------------------------------------------------------------------------
function manejarCupones(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $authMiddleware->requiereMeseroOAdministrador();
    $pdo = Conexion::obtener();
    $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();

    if (($ruta === '' || $ruta === 'listar') && $metodo === 'GET') {
        $stmt = $pdo->query('SELECT c.*, u.nombre AS creado_por_nombre FROM cupones_descuento c LEFT JOIN usuarios u ON u.id_usuario = c.creado_por ORDER BY c.codigo');
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Cupones obtenidos.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $codigo = $cuerpo['codigo'] ?? '';
        $tipo = $cuerpo['tipo_descuento'] ?? 'porcentaje';
        $valor = isset($cuerpo['valor_descuento']) ? (float) $cuerpo['valor_descuento'] : 0.00;
        $minimo = isset($cuerpo['monto_minimo']) ? (float) $cuerpo['monto_minimo'] : 0.00;
        $maximo = isset($cuerpo['descuento_maximo']) && $cuerpo['descuento_maximo'] !== '' ? (float) $cuerpo['descuento_maximo'] : null;
        $inicio = $cuerpo['fecha_inicio'] ?? '';
        $fin = $cuerpo['fecha_fin'] ?? null;
        $uso_max = isset($cuerpo['uso_maximo']) && $cuerpo['uso_maximo'] !== '' ? (int) $cuerpo['uso_maximo'] : null;
        $uso_cliente = isset($cuerpo['uso_por_cliente']) ? (int) $cuerpo['uso_por_cliente'] : 1;

        if ($codigo === '') {
            return errorJson('Se requiere el código del cupón.', 400);
        }
        if (!in_array($tipo, ['porcentaje', 'monto_fijo'], true)) {
            return errorJson('Tipo de descuento inválido.', 400);
        }
        if ($inicio === '') {
            return errorJson('Se requiere fecha de inicio.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO cupones_descuento (codigo, tipo_descuento, valor_descuento, monto_minimo, descuento_maximo, fecha_inicio, fecha_fin, uso_maximo, uso_por_cliente, activo, creado_por)
                VALUES (:cd, :td, :vd, :mm, :dm, :fi, :ff, :um, :uc, 1, :cp)
            ');
            $stmt->execute([
                ':cd' => $codigo,
                ':td' => $tipo,
                ':vd' => $valor,
                ':mm' => $minimo,
                ':dm' => $maximo,
                ':fi' => $inicio,
                ':ff' => $fin,
                ':um' => $uso_max,
                ':uc' => $uso_cliente,
                ':cp' => $id_usuario,
            ]);
            return ['exito' => true, 'mensaje' => 'Cupón creado.', 'codigo' => 200, 'datos' => ['id_cupon' => (int) $pdo->lastInsertId()]];
        } catch (\PDOException $e) {
            return errorJson('Error al crear el cupón.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'PUT') {
        $id_cupon = (int) $c[1];
        $campos = [];
        $params = [];
        foreach (['codigo', 'tipo_descuento', 'valor_descuento', 'monto_minimo', 'descuento_maximo', 'fecha_inicio', 'fecha_fin', 'uso_maximo', 'uso_por_cliente', 'activo'] as $campo) {
            if (isset($cuerpo[$campo])) {
                $campos[] = $campo . ' = :' . str_replace('_', '', $campo);
                $params[':' . str_replace('_', '', $campo)] = $cuerpo[$campo];
            }
        }
        if ($campos === []) {
            return errorJson('No hay campos para actualizar.', 400);
        }
        $params[':id'] = $id_cupon;
        try {
            $stmt = $pdo->prepare('UPDATE cupones_descuento SET ' . implode(', ', $campos) . ' WHERE id_cupon = :id');
            $stmt->execute($params);
            return ['exito' => true, 'mensaje' => 'Cupón actualizado.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al actualizar el cupón.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id_cupon = (int) $c[1];
        $stmt = $pdo->prepare('UPDATE cupones_descuento SET activo = 0 WHERE id_cupon = ?');
        $stmt->execute([$id_cupon]);
        return ['exito' => true, 'mensaje' => 'Cupón desactivado.', 'codigo' => 200];
    }

    return errorJson('Endpoint de cupones no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Calificaciones de platos (RF-91)
// -------------------------------------------------------------------------
function manejarCalificaciones(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $pdo = Conexion::obtener();

    // GET público: listar calificaciones de un producto
    if (($ruta === '' || $ruta === 'listar') && $metodo === 'GET') {
        $id_producto = isset($query['id_producto']) ? (int) $query['id_producto'] : 0;
        if ($id_producto <= 0) {
            return errorJson('Se requiere id_producto.', 400);
        }
        $stmt = $pdo->prepare('
            SELECT cp.*, u.nombre AS nombre_usuario
            FROM calificaciones_platos cp
            JOIN usuarios u ON u.id_usuario = cp.id_usuario
            WHERE cp.id_producto = ? AND cp.activo = 1
            ORDER BY cp.fecha_calificacion DESC
        ');
        $stmt->execute([$id_producto]);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Calificaciones obtenidas.', 'codigo' => 200];
    }

    // POST: crear calificación (cliente autenticado)
    if ($ruta === '' && $metodo === 'POST') {
        $authMiddleware->requiereAutenticacion();
        $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
        $id_producto = isset($cuerpo['id_producto']) ? (int) $cuerpo['id_producto'] : 0;
        $puntuacion = isset($cuerpo['puntuacion']) ? (int) $cuerpo['puntuacion'] : 0;
        $comentario = $cuerpo['comentario'] ?? null;

        if ($id_producto <= 0) {
            return errorJson('id_producto inválido.', 400);
        }
        if ($puntuacion < 1 || $puntuacion > 5) {
            return errorJson('La puntuación debe estar entre 1 y 5.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO calificaciones_platos (id_producto, id_usuario, puntuacion, comentario, activo)
                VALUES (:ip, :iu, :p, :c, 1)
                ON DUPLICATE KEY UPDATE puntuacion = VALUES(puntuacion), comentario = VALUES(comentario), fecha_calificacion = CURRENT_TIMESTAMP, activo = 1
            ');
            $stmt->execute([
                ':ip' => $id_producto,
                ':iu' => $id_usuario,
                ':p' => $puntuacion,
                ':c' => $comentario,
            ]);
            return ['exito' => true, 'mensaje' => 'Calificación guardada.', 'codigo' => 200];
        } catch (\PDOException $e) {
            return errorJson('Error al guardar la calificación.', 500);
        }
    }

    return errorJson('Endpoint de calificaciones no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Preferencias y alergias del cliente (RF-92)
// -------------------------------------------------------------------------
function manejarPreferencias(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $authMiddleware->requiereAutenticacion();
    $pdo = Conexion::obtener();
    $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();

    if (($ruta === '' || $ruta === 'listar') && $metodo === 'GET') {
        $stmt = $pdo->prepare('SELECT * FROM preferencias_cliente WHERE id_usuario = ? AND activo = 1 ORDER BY tipo, descripcion');
        $stmt->execute([$id_usuario]);
        return ['exito' => true, 'datos' => $stmt->fetchAll(), 'mensaje' => 'Preferencias obtenidas.', 'codigo' => 200];
    }

    if ($ruta === '' && $metodo === 'POST') {
        $tipo = $cuerpo['tipo'] ?? 'preferencia';
        $descripcion = $cuerpo['descripcion'] ?? '';
        if (!in_array($tipo, ['alergia', 'preferencia', 'restriccion'], true)) {
            return errorJson('Tipo inválido.', 400);
        }
        if ($descripcion === '') {
            return errorJson('Se requiere la descripción.', 400);
        }
        try {
            $stmt = $pdo->prepare('
                INSERT INTO preferencias_cliente (id_usuario, tipo, descripcion, activo)
                VALUES (:iu, :t, :d, 1)
            ');
            $stmt->execute([
                ':iu' => $id_usuario,
                ':t' => $tipo,
                ':d' => $descripcion,
            ]);
            return ['exito' => true, 'mensaje' => 'Preferencia guardada.', 'codigo' => 200, 'datos' => ['id_preferencia' => (int) $pdo->lastInsertId()]];
        } catch (\PDOException $e) {
            return errorJson('Error al guardar la preferencia.', 500);
        }
    }

    if (preg_match('/^(\d+)$/', $ruta, $c) && $metodo === 'DELETE') {
        $id_preferencia = (int) $c[1];
        $stmt = $pdo->prepare('UPDATE preferencias_cliente SET activo = 0 WHERE id_preferencia = ? AND id_usuario = ?');
        $stmt->execute([$id_preferencia, $id_usuario]);
        return ['exito' => true, 'mensaje' => 'Preferencia desactivada.', 'codigo' => 200];
    }

    return errorJson('Endpoint de preferencias no encontrado.', 404);
}

// -------------------------------------------------------------------------
// Puntos de fidelidad (RF-90)
// -------------------------------------------------------------------------
function manejarPuntosFidelidad(string $ruta, string $metodo, array $query, array $cuerpo, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth): array
{
    $pdo = Conexion::obtener();

    // GET: consultar puntos del cliente autenticado
    if (($ruta === '' || $ruta === 'mis-puntos') && $metodo === 'GET') {
        $authMiddleware->requiereAutenticacion();
        $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
        $stmt = $pdo->prepare('SELECT * FROM puntos_fidelidad WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $puntos = $stmt->fetch();
        if (!$puntos) {
            return ['exito' => true, 'datos' => ['puntos_disponibles' => 0, 'puntos_acumulados' => 0, 'puntos_canjeados' => 0], 'mensaje' => 'El cliente no tiene puntos acumulados.', 'codigo' => 200];
        }
        return ['exito' => true, 'datos' => $puntos, 'mensaje' => 'Puntos obtenidos.', 'codigo' => 200];
    }

    // PUT: actualizar puntos (solo admin/encargado)
    if ($ruta === '' && $metodo === 'PUT') {
        $authMiddleware->requiereMeseroOAdministrador();
        $id_usuario = isset($cuerpo['id_usuario']) ? (int) $cuerpo['id_usuario'] : 0;
        $puntos = isset($cuerpo['puntos']) ? (int) $cuerpo['puntos'] : 0;
        $tipo = $cuerpo['tipo'] ?? 'sumar'; // sumar, restar, set

        if ($id_usuario <= 0) {
            return errorJson('id_usuario requerido.', 400);
        }
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT puntos_disponibles, puntos_acumulados, puntos_canjeados FROM puntos_fidelidad WHERE id_usuario = ?');
            $stmt->execute([$id_usuario]);
            $actual = $stmt->fetch();

            if (!$actual) {
                $stmt = $pdo->prepare('INSERT INTO puntos_fidelidad (id_usuario, puntos_disponibles, puntos_acumulados, puntos_canjeados) VALUES (:iu, 0, 0, 0)');
                $stmt->execute([':iu' => $id_usuario]);
                $disponibles = 0;
                $acumulados = 0;
                $canjeados = 0;
            } else {
                $disponibles = (int) $actual['puntos_disponibles'];
                $acumulados = (int) $actual['puntos_acumulados'];
                $canjeados = (int) $actual['puntos_canjeados'];
            }

            if ($tipo === 'sumar') {
                $disponibles += $puntos;
                $acumulados += $puntos;
            } elseif ($tipo === 'restar') {
                $disponibles = max(0, $disponibles - $puntos);
            } elseif ($tipo === 'set') {
                $disponibles = $puntos;
            }

            $stmt = $pdo->prepare('
                INSERT INTO puntos_fidelidad (id_usuario, puntos_disponibles, puntos_acumulados, puntos_canjeados)
                VALUES (:iu, :d, :a, :c)
                ON DUPLICATE KEY UPDATE puntos_disponibles = :d, puntos_acumulados = :a, puntos_canjeados = :c, fecha_actualizacion = CURRENT_TIMESTAMP
            ');
            $stmt->execute([
                ':iu' => $id_usuario,
                ':d' => $disponibles,
                ':a' => $acumulados,
                ':c' => $canjeados,
            ]);
            $pdo->commit();
            return ['exito' => true, 'mensaje' => 'Puntos actualizados.', 'codigo' => 200, 'datos' => ['puntos_disponibles' => $disponibles]];
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return errorJson('Error al actualizar los puntos.', 500);
        }
    }

    return errorJson('Endpoint de puntos de fidelidad no encontrado.', 404);
}

// -------------------------------------------------------------------------
// RF-84: Estado de entrega de delivery
// -------------------------------------------------------------------------
if (false) {} // placeholder para mantener estructura

// -------------------------------------------------------------------------
// RF-85: Facturación electrónica (básica, sin proveedor externo)
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-87: Cierre de caja diario
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-89: Historial de cliente y platos favoritos
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-93: Reservas recurrentes
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-94 a RF-96: Notificaciones
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-97 a RF-100: Reportes avanzados
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-102: Sucursales múltiples
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-103: Respaldos de base de datos
// -------------------------------------------------------------------------
// -------------------------------------------------------------------------
// RF-105: Roles y permisos avanzados
// -------------------------------------------------------------------------

function errorJson(string $mensaje, int $codigo): array
{
    http_response_code($codigo);
    return ['exito' => false, 'datos' => null, 'mensaje' => $mensaje, 'codigo' => $codigo];
}

http_response_code($codigo_http);
echo json_encode($respuesta ?? ['exito' => false, 'mensaje' => 'Respuesta vacía.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
