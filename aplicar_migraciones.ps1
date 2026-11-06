# ============================================================================
# Script para aplicar TODAS las migraciones a la MySQL de Railway
# REQUISITO: Tener `railway connect` ejecutado en OTRA terminal (MySQL seleccionado)
# Ejecutar con: powershell -ExecutionPolicy Bypass -File aplicar_migraciones.ps1
# ============================================================================

$ErrorActionPreference = "Continue"

# ---- Credenciales MySQL de Railway (obtenidas con `railway variables`) ----
$mysqlUser   = "root"
$mysqlPass   = "OINakvGeYbODOcPuVJXGBYraXqDRumnO"
$mysqlDb     = "railway"
$mysqlHost   = "127.0.0.1"
$mysqlPorts  = @(3306, 3307, 3308, 3309)  # Puertos posibles del túnel

# Ruta al cliente mysql de XAMPP
$mysqlExe = "C:\xampp\mysql\bin\mysql.exe"

if (!(Test-Path $mysqlExe)) {
    Write-Host "ERROR: No se encontró mysql.exe en $mysqlExe" -ForegroundColor Red
    exit 1
}

# Agregar XAMPP al PATH
$env:PATH = "C:\xampp\mysql\bin;" + $env:PATH

# Función para probar conexión
function Test-MySqlConexion {
    param([int]$puerto)
    $r = & $mysqlExe -h $mysqlHost -P $puerto -u $mysqlUser -p$mysqlPass $mysqlDb -e "SELECT 1;" 2>&1
    return $LASTEXITCODE -eq 0
}

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  BUSCANDO TÚNEL DE RAILWAY (MySQL)" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan

$mysqlPort = $null
foreach ($p in $mysqlPorts) {
    if (Test-MySqlConexion $p) {
        $mysqlPort = $p
        Write-Host ("Túnel encontrado en puerto " + $p) -ForegroundColor Green
        break
    }
}

if ($null -eq $mysqlPort) {
    Write-Host "" -ForegroundColor Red
    Write-Host "ERROR: No se encontró el túnel de Railway." -ForegroundColor Red
    Write-Host "En OTRA terminal de PowerShell ejecuta:" -ForegroundColor Yellow
    Write-Host "  $env:PATH = ""C:\xampp\mysql\bin;"" + $env:PATH" -ForegroundColor Yellow
    Write-Host "  railway connect" -ForegroundColor Yellow
    Write-Host "Selecciona MySQL y deja esa terminal abierta." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Luego volvé a correr este script." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  APLICANDO MIGRACIONES (001 → 017)" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan

$migraciones = @(
    "migrations\001_create_schema.sql",
    "migrations\002_migraciones_control.sql",
    "migrations\003_datos_iniciales.sql",
    "migrations\004_stock_y_disponibilidad.sql",
    "migrations\005_pedidos_y_senas.sql",
    "migrations\006_historial_y_reportes.sql",
    "migrations\007_datos_prueba.sql",
    "migrations\008_recuperar_contrasena_y_perfil.sql",
    "migrations\009_configuracion_y_limites_pedidos.sql",
    "migrations\010_cupones_descuento.sql",
    "migrations\011_calificaciones_preferencias_fidelidad.sql",
    "migrations\012_delivery_repartidores.sql",
    "migrations\013_rf_pendientes.sql",
    "migrations\014_cloudinary_imagenes_productos.sql",
    "migrations\015_datos_prueba_completos.sql",
    "migrations\016_datos_finales.sql",
    "migrations\017_reparacion_productos.sql"
)

function EjecutarMigracion([string]$archivo) {
    if (!(Test-Path $archivo)) {
        Write-Host "  [SKIP] No existe: $archivo" -ForegroundColor Yellow
        return
    }
    Write-Host "  Ejecutando: $archivo ..." -NoNewline
    $resultado = Get-Content $archivo -Raw | & $mysqlExe -h $mysqlHost -P $mysqlPort -u $mysqlUser -p$mysqlPass $mysqlDb 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host " OK" -ForegroundColor Green
    } else {
        Write-Host " ERROR" -ForegroundColor Red
        $resultado | ForEach-Object { if ($_ -ne "") { Write-Host "    $_" -ForegroundColor DarkRed } }
    }
}

foreach ($m in $migraciones) {
    EjecutarMigracion $m
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  VERIFICANDO PRODUCTOS EN LA CARTA" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
$conteo = & $mysqlExe -h $mysqlHost -P $mysqlPort -u $mysqlUser -p$mysqlPass $mysqlDb -e "SELECT COUNT(*) AS total_productos_activos FROM productos WHERE activo=1;" 2>&1
Write-Host $conteo

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  LISTO" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Recargá en el navegador:" -ForegroundColor Green
Write-Host "  https://green-production-3cc5.up.railway.app/carta.html" -ForegroundColor Green
Write-Host "  https://green-production-3cc5.up.railway.app/" -ForegroundColor Green
