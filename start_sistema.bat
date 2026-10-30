@echo off
cd /d "C:\xampp"

echo ========================================
echo  Iniciando Green en la Casa Blanca
echo ========================================
echo.

echo Iniciando MySQL...
start "MySQL" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --user=root --console

echo Esperando a que MySQL esté listo...
timeout /t 5 /nobreak > nul

echo Iniciando servidor web en puerto 8082...
start "Servidor Web" "C:\xampp\php\php.exe" -S localhost:8082 -t "C:\xampp\htdocs\Yensel\Morday\public" "C:\xampp\htdocs\Yensel\Morday\public\router.php"

echo.
echo ========================================
echo  SERVIDORES INICIADOS
echo ========================================
echo.
echo  Sitio web:    http://localhost:8082/
echo  API:          http://localhost:8082/api/
echo  phpMyAdmin:   http://localhost:8082/../phpmyadmin/
echo.
echo  Mantenga esta ventana abierta para usar el sistema.
echo  Presione cualquier tecla para cerrar los servidores...
echo ========================================
pause > nul

taskkill /IM mysqld.exe /F 2>nul
taskkill /IM php.exe /F 2>nul
