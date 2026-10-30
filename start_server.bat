@echo off
cd /d "C:\xampp\php"
php -S localhost:8082 -t "C:\xampp\htdocs\Yensel\Morday\public" "C:\xampp\htdocs\Yensel\Morday\public\router.php"
pause
