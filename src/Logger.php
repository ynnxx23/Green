<?php

declare(strict_types=1);

namespace Restaurante;

class Logger
{
    private static ?string $logDir = null;

    public static function init(string $logDir): void
    {
        self::$logDir = rtrim($logDir, '/');
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0755, true);
        }
    }

    public static function error(string $mensaje, array $contexto = []): void
    {
        self::escribir('ERROR', $mensaje, $contexto);
    }

    public static function info(string $mensaje, array $contexto = []): void
    {
        self::escribir('INFO', $mensaje, $contexto);
    }

    public static function warning(string $mensaje, array $contexto = []): void
    {
        self::escribir('WARNING', $mensaje, $contexto);
    }

    private static function escribir(string $nivel, string $mensaje, array $contexto): void
    {
        if (self::$logDir === null) {
            return;
        }
        $archivo = self::$logDir . '/' . date('Y-m-d') . '.log';
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
        $uri = $_SERVER['REQUEST_URI'] ?? '-';
        $linea = sprintf("[%s] [%s] IP=%s URI=%s %s %s\n", $fecha, $nivel, $ip, $uri, $mensaje, $contexto !== [] ? json_encode($contexto, JSON_UNESCAPED_UNICODE) : '');
        @file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
    }
}
