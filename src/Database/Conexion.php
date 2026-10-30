<?php

declare(strict_types=1);

namespace Restaurante\Database;

use PDO;
use PDOException;
use Restaurante\Config\Config;

final class Conexion
{
    private static ?PDO $instancia = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function obtener(): PDO
    {
        if (self::$instancia === null) {
            $db = Config::db();

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$instancia = new PDO(
                $dsn,
                $db['user'],
                $db['password'],
                $opciones
            );
        }

        return self::$instancia;
    }

    public static function cerrar(): void
    {
        self::$instancia = null;
    }
}
