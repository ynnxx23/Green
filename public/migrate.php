<?php
/**
 * migrate.php — Script temporal para aplicar migraciones SQL desde el navegador
 * en Railway, donde no se puede usar `railway connect` ni el SQL Dashboard
 * (archivo demasiado largo).
 *
 * USO:
 *  1. Subir este archivo a public/migrate.php en Railway
 *  2. Visitar: https://green-production-3cc5.up.railway.app/migrate.php
 *  3. Hacer clic en "APLICAR MIGRACIONES"
 *  4. Borrar este archivo después de usarlo (seguridad)
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

// ---- Credenciales: Railway provee MYSQLHOST/MYSQLUSER/MYSQLPASSWORD/MYSQLDATABASE ----
$host     = $_ENV['MYSQLHOST']     ?? $_ENV['DB_HOST']     ?? '127.0.0.1';
$port     = (int)($_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT']     ?? 3306);
$dbname   = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME']   ?? 'railway';
$user     = $_ENV['MYSQLUSER']     ?? $_ENV['DB_USER']     ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';

// ---- Listado de migraciones en orden ----
$migraciones = [
    '001_create_schema.sql',
    '002_migraciones_control.sql',
    '003_datos_iniciales.sql',
    '004_stock_y_disponibilidad.sql',
    '005_pedidos_y_senas.sql',
    '006_historial_y_reportes.sql',
    '007_datos_prueba.sql',
    '008_recuperar_contrasena_y_perfil.sql',
    '009_configuracion_y_limites_pedidos.sql',
    '010_cupones_descuento.sql',
    '011_calificaciones_preferencias_fidelidad.sql',
    '012_delivery_repartidores.sql',
    '013_rf_pendientes.sql',
    '014_cloudinary_imagenes_productos.sql',
    '015_datos_prueba_completos.sql',
    '016_datos_finales.sql',
    '017_reparacion_productos.sql',
];

$migrationsDir = __DIR__ . '/../migrations';

// ---- Función para dividir SQL en sentencias ----
function splitSql(string $sql): array
{
    $lineas = explode("\n", $sql);
    $buffer = '';
    $sentencias = [];

    foreach ($lineas as $linea) {
        $trim = trim($linea);
        if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
            continue;
        }
        // Ignorar bloques de comentario /* ... */ simples de una línea
        if (str_starts_with($trim, '/*') && str_ends_with($trim, '*/')) {
            continue;
        }

        $buffer .= $linea . "\n";

        if (str_ends_with(trim($buffer), ';')) {
            $sentencias[] = trim($buffer);
            $buffer = '';
        }
    }

    if (trim($buffer) !== '') {
        $sentencias[] = trim($buffer);
    }

    return $sentencias;
}

// ---- Ejecutar migraciones ----
$resultados = [];
$aplicadas = 0;
$errores = 0;

if (isset($_POST['aplicar'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $pdo->exec("SET SQL_MODE = ''"); // evitar modos estrictos que bloqueen ALTER ... IF NOT EXISTS

        // Asegurar tabla de control de migraciones
        $pdo->exec("CREATE TABLE IF NOT EXISTS `migraciones_aplicadas` (
            `version` VARCHAR(50) NOT NULL,
            `fecha_aplicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        foreach ($migraciones as $archivo) {
            $ruta = $migrationsDir . '/' . $archivo;
            $version = pathinfo($archivo, PATHINFO_FILENAME);

            if (!is_file($ruta)) {
                $resultados[] = ['archivo' => $archivo, 'estado' => 'SKIP', 'mensaje' => 'No encontrado'];
                continue;
            }

            // Ya aplicada según la tabla de control
            $chk = $pdo->prepare('SELECT COUNT(*) FROM migraciones_aplicadas WHERE version = ?');
            $chk->execute([$version]);
            if ((int)$chk->fetchColumn() > 0) {
                $resultados[] = ['archivo' => $archivo, 'estado' => 'OK', 'mensaje' => 'Ya aplicada (control)'];
                $aplicadas++;
                continue;
            }

            $sql = file_get_contents($ruta);
            $sentencias = splitSql($sql);
            $ok = true;
            $ultimoError = '';

            foreach ($sentencias as $sentencia) {
                if (trim($sentencia) === '') {
                    continue;
                }
                try {
                    $pdo->exec($sentencia);
                } catch (PDOException $e) {
                    $ok = false;
                    $ultimoError = $e->getMessage();
                    // No abortar todo: INSERT IGNORE y CREATE IF NOT EXISTS ya manejan duplicados,
                    // pero si hay un error real lo reportamos y seguimos con la siguiente migración
                    break;
                }
            }

            if ($ok) {
                $pdo->prepare('INSERT IGNORE INTO migraciones_aplicadas (version) VALUES (?)')->execute([$version]);
                $resultados[] = ['archivo' => $archivo, 'estado' => 'OK', 'mensaje' => count($sentencias) . ' sentencia(s) ejecutada(s)'];
                $aplicadas++;
            } else {
                $resultados[] = ['archivo' => $archivo, 'estado' => 'ERROR', 'mensaje' => $ultimoError];
                $errores++;
            }
        }

        $pdo = null;
    } catch (PDOException $e) {
        $resultados[] = ['archivo' => 'CONEXIÓN', 'estado' => 'ERROR', 'mensaje' => $e->getMessage()];
        $errores++;
    }
}

// ---- Verificar productos ----
$conteoProductos = null;
$conteoStock = null;
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
    $conteoProductos = (int)$stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM stock_producto WHERE activo = 1");
    $conteoStock = (int)$stmt->fetchColumn();
    $pdo = null;
} catch (PDOException $e) {
    $conteoProductos = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Migrar DB — Green en la Casa Blanca</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 2rem; background: #f5f5f5; color: #1a1a1a; }
  .max { max-width: 700px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
  h1 { font-size: 1.5rem; margin-top: 0; }
  .conn { background: #eef; padding: 1rem; border-radius: 6px; font-size: 0.9rem; margin-bottom: 1.5rem; }
  .conn code { background: #ddd; padding: 0 4px; border-radius: 4px; }
  button { background: #0a6; color: #fff; border: none; padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
  button:disabled { background: #999; cursor: not-allowed; }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
  th { background: #f9f9f9; }
  .ok { color: #0a6; font-weight: 600; }
  .err { color: #c00; font-weight: 600; }
  .skip { color: #999; }
  .resumen { background: #eef; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; }
  .warn { background: #fee; color: #833; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; font-size: 0.9rem; }
  a { color: #0a6; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .mono { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.85rem; }
</style>
</head>
<body>
<div class="max">
  <h1>🔧 Migrar Base de Datos — Railway</h1>
  <p>Aplica las migraciones SQL pendientes (001 → 017) contra la MySQL de Railway desde el navegador.</p>

  <div class="conn">
    <strong>Conexión:</strong><br>
    Host: <code><?= htmlspecialchars($host) ?></code> &nbsp;|&nbsp;
    Puerto: <code><?= htmlspecialchars((string)$port) ?></code> &nbsp;|&nbsp;
    DB: <code><?= htmlspecialchars($dbname) ?></code> &nbsp;|&nbsp;
    Usuario: <code><?= htmlspecialchars($user) ?></code>
  </div>

  <?php if (!isset($_POST['aplicar'])): ?>
    <form method="post">
      <button type="submit" name="aplicar" value="1">▶ APLICAR MIGRACIONES (001 → 017)</button>
    </form>
    <p class="warn">
      <strong>⚠ Seguridad:</strong> Este script es temporal. Bórralo después de usarlo:<br>
      <code>rm public/migrate.php</code> o elimínalo desde el panel de archivos de Railway.
    </p>
  <?php else: ?>
    <div class="resumen">
      <strong>Resultado:</strong> <?= $aplicadas ?> aplicada(s) · <?= $errores ?> error(es)
    </div>

    <table>
      <thead><tr><th>Archivo</th><th>Estado</th><th>Detalle</th></tr></thead>
      <tbody>
      <?php foreach ($resultados as $r): ?>
        <tr>
          <td class="mono"><?= htmlspecialchars($r['archivo']) ?></td>
          <td class="<?= $r['estado'] === 'OK' ? 'ok' : ($r['estado'] === 'ERROR' ? 'err' : 'skip') ?>">
            <?= htmlspecialchars($r['estado']) ?>
          </td>
          <td class="mono"><?= htmlspecialchars($r['mensaje']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="resumen">
      <strong>Productos activos en carta:</strong> <?= is_int($conteoProductos) ? (string)$conteoProductos : htmlspecialchars($conteoProductos) ?><br>
      <strong>Stock activo:</strong> <?= is_int($conteoStock) ? (string)$conteoStock : htmlspecialchars($conteoStock) ?>
    </div>

    <?php if (is_int($conteoProductos) && $conteoProductos > 0): ?>
      <p class="ok">✅ ¡Listo! Recarga la carta en el navegador:</p>
      <ul>
        <li><a href="https://green-production-3cc5.up.railway.app/carta.html" target="_blank">carta.html</a></li>
        <li><a href="https://green-production-3cc5.up.railway.app/" target="_blank">index.html (inicio)</a></li>
      </ul>
    <?php endif; ?>

    <p><a href="?">Volver</a></p>
  <?php endif; ?>
</div>
</body>
</html>
```
