<?php

declare(strict_types=1);

namespace Restaurante;

use Restaurante\Config\Config;

class ConfiguracionSesion
{
    public static function aplicar(): void
    {
        $secure = Config::sessionCookieSecure();
        $sameSite = Config::sessionCookieSameSite();

        session_name('restaurante_session');

        ini_set('session.cookie_lifetime', '0');
        ini_set('session.gc_maxlifetime', '1800');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');

        if ($secure) {
            ini_set('session.cookie_secure', '1');
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite,
        ]);
    }
}
