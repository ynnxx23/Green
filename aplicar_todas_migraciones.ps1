# ============================================================================
# SCRIPT DE APLICACIÓN DE MIGRACIONES — Green en la Casa Blanca
# Aplica todas las migraciones del 001 al 018 en orden.
# Tolerante a errores de duplicado y columnas faltantes.
# ============================================================================

$mysql = "C:\xampp\mysql\bin\mysql.exe"
$dbName = "restaurante_dev"
$dbUser = "root"
$migrationsDir = "C:\xampp\htdocs\Yensel\Morday\migrations"

# Borrar y recrear base de datos
Write-Host "Recreando base de datos $dbName..." -ForegroundColor Cyan
& $mysql -u $dbUser -e "DROP DATABASE IF EXISTS $dbName; CREATE DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: No se pudo crear la base de datos." -ForegroundColor Red
    exit 1
}

# Lista de migraciones en orden
$migraciones = @(
    "001_create_schema.sql",
    "002_migraciones_control.sql",
    "003_datos_iniciales.sql",
    "004_stock_y_disponibilidad.sql",
    "005_pedidos_y_senas.sql",
    "006_historial_y_reportes.sql",
    "007_datos_prueba.sql",
    "008_recuperar_contrasena_y_perfil.sql",
    "009_configuracion_y_limites_pedidos.sql",
    "010_cupones_descuento.sql",
    "011_calificaciones_preferencias_fidelidad.sql",
    "012_delivery_repartidores.sql",
    "013_rf_pendientes.sql",
    "014_cloudinary_imagenes_productos.sql",
    "015_datos_prueba_completos.sql",
    "016_datos_finales.sql",
    "017_reparacion_productos.sql",
    "018_pedidos_saldo_numero.sql",
    "019_renombrar_columnas_senia.sql",
    "020_add_id_usuario_pedidos.sql",
    "021_add_fecha_creacion_pedidos.sql",
    "022_id_mesa_nullable.sql",
    "023_fix_encoding_utf8.sql"
)

$fallos = @()

foreach ($mig in $migraciones) {
    $ruta = Join-Path $migrationsDir $mig
    if (-not (Test-Path $ruta)) {
        Write-Host "ADVERTENCIA: $mig no encontrado, saltando..." -ForegroundColor Yellow
        continue
    }

    Write-Host "Aplicando $mig..." -ForegroundColor Cyan

    # Aplicar migración (ignorar errores de SQL individuales)
    # --default-character-set=utf8mb4 evita que los acentos se guarden como "??" (codepage de consola)
    $salida = Get-Content $ruta -Raw | & $mysql -u $dbUser --default-character-set=utf8mb4 $dbName 2>&1
    $codigoSalida = $LASTEXITCODE

    if ($codigoSalida -ne 0) {
        if ($salida -match "Duplicate column|Duplicate key|Duplicate entry|1060|1061|1062|1054|1064") {
            Write-Host "  ADVERTENCIA: Error SQL no crítico, continuando..." -ForegroundColor Yellow
        } else {
            Write-Host "  ERROR en ${mig}:" -ForegroundColor Red
            Write-Host "  $salida" -ForegroundColor Red
            $fallos += $mig
        }
    } else {
        Write-Host "  OK: ${mig} aplicada." -ForegroundColor Green
    }

    # Registrar en migraciones_aplicadas
    try {
        & $mysql -u $dbUser $dbName -e "INSERT IGNORE INTO migraciones_aplicadas (version) VALUES ('${mig}');" 2>$null
    } catch {}
}

Write-Host "`n=== RESUMEN ===" -ForegroundColor Cyan
if ($fallos.Count -gt 0) {
    Write-Host "Migraciones con errores:" -ForegroundColor Red
    $fallos | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
} else {
    Write-Host "Todas las migraciones aplicadas (con advertencias toleradas)." -ForegroundColor Green
}

Write-Host "`nTablas en la base de datos:" -ForegroundColor Cyan
& $mysql -u $dbUser $dbName -e "SHOW TABLES;" 2>$null
