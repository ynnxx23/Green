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
$auth = new ServicioDeAutenticacion($usuarioDao, $intentosDao);
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

            if (in_array($ruta_interna, ['login', 'registro'], true) && $metodo === 'POST') {
                $cliente_id = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
                $endpoint = $metodo . ':' . $uri;
                $rateLimiter->consumir($cliente_id, $endpoint);
            }

            $respuesta = $authController->manejar($ruta_interna, $metodo);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

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

            $respuesta = $reservaController->manejar($ruta_interna, $metodo, $query_params);
            $codigo_http = $respuesta['codigo'] ?? 200;
            break;

        case str_starts_with($uri, '/api/pedidos'):
            $ruta_interna = ltrim(substr($uri, strlen('/api/pedidos')), '/');
            $query_params = $_GET ?? [];

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
            $cuerpo = leerCuerpoJson();
            if ($metodo === 'PUT' || $metodo === 'DELETE') {
                $_REQUEST = array_merge($_REQUEST, $cuerpo);
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

function errorJson(string $mensaje, int $codigo): array
{
    http_response_code($codigo);
    return ['exito' => false, 'datos' => null, 'mensaje' => $mensaje, 'codigo' => $codigo];
}

http_response_code($codigo_http);
echo json_encode($respuesta ?? ['exito' => false, 'mensaje' => 'Respuesta vacía.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
