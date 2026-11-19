@echo off
cd /d "C:\xampp\htdocs\Yensel\Morday"

echo Deteniendo servidores previos...
taskkill /IM php.exe /F >nul 2>&1
taskkill /IM mysqld.exe /F >nul 2>&1
timeout /t 2 /nobreak >nul

echo Iniciando MySQL...
start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --user=root --console

echo Esperando MySQL...
timeout /t 5 /nobreak >nul

echo Iniciando servidor PHP en puerto 8082...
start "" "C:\xampp\php\php.exe" -S localhost:8082 -t "C:\xampp\htdocs\Yensel\Morday\public" "C:\xampp\htdocs\Yensel\Morday\public\router.php"

timeout /t 3 /nobreak >nul

echo Verificando servidor...
"C:\xampp\curl\bin\curl.exe" -s -o nul -w "%{http_code}" http://localhost:8082/ 2>nul || echo "curl no disponible, probando con powershell..."

echo.
echo ========================================
echo SERVIDORES INICIADOS
echo ========================================
echo Sitio web: http://localhost:8082/
echo API: http://localhost:8082/api/
echo.
echo Mantenga esta ventana abierta.
pause
