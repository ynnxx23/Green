<?php

declare(strict_types=1);

namespace Restaurante\Config;

final class Config
{
    private static array $valores = [];

    private const DEFAULTS = [
        'APP_ENV' => 'development',
        'APP_URL' => 'http://localhost/restaurante/public',
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'restaurante',
        'DB_USER' => 'root',
        'DB_PASSWORD' => '',
        'SESSION_COOKIE_SECURE' => '0',
        'SESSION_COOKIE_SAMESITE' => 'Lax',
        'RATE_LIMIT_CAPACITY' => '60',
        'RATE_LIMIT_REFILL_RATE' => '1',
    ];

    private function __construct()
    {
    }

    public static function obtener(string $clave, string $valorPorDefecto = ''): string
    {
        if (isset(self::$valores[$clave])) {
            return self::$valores[$clave];
        }

        $valor = $_ENV[$clave] ?? getenv($clave) ?? $_SERVER[$clave] ?? self::DEFAULTS[$clave] ?? $valorPorDefecto;
        self::$valores[$clave] = (string) $valor;

        return self::$valores[$clave];
    }

    public static function obtenerEntero(string $clave, int $valorPorDefecto = 0): int
    {
        return (int) self::obtener($clave, (string) $valorPorDefecto);
    }

    public static function obtenerBooleano(string $clave, bool $valorPorDefecto = false): bool
    {
        $valor = strtolower(self::obtener($clave, $valorPorDefecto ? '1' : '0'));

        return in_array($valor, ['1', 'true', 'yes', 'on'], true);
    }

    public static function db(): array
    {
        return [
            'host' => self::obtener('DB_HOST'),
            'port' => self::obtenerEntero('DB_PORT', 3306),
            'name' => self::obtener('DB_NAME'),
            'user' => self::obtener('DB_USER'),
            'password' => self::obtener('DB_PASSWORD'),
        ];
    }

    public static function appUrl(): string
    {
        return self::obtener('APP_URL');
    }

    public static function esProduccion(): bool
    {
        return self::obtener('APP_ENV') === 'production';
    }

    public static function sessionCookieSecure(): bool
    {
        return self::obtenerBooleano('SESSION_COOKIE_SECURE', self::esProduccion());
    }

    public static function sessionCookieSameSite(): string
    {
        return self::obtener('SESSION_COOKIE_SAMESITE', 'Lax');
    }
}
