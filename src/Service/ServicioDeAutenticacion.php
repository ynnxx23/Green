<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\IntentosLoginDao;
use Restaurante\Dao\UsuarioDao;
use Restaurante\Model\Usuario;

class ServicioDeAutenticacion
{
    private UsuarioDao $usuarioDao;
    private IntentosLoginDao $intentosDao;

    private const MAX_INTENTOS_FALLOS = 5;
    private const VENTANA_BLOQUEO_MINUTOS = 15;

    public function __construct(UsuarioDao $usuarioDao, IntentosLoginDao $intentosDao)
    {
        $this->usuarioDao = $usuarioDao;
        $this->intentosDao = $intentosDao;
    }

    public function registrar(string $nombre, string $email, string $contrasena_plana, int $id_rol = 1): Usuario
    {
        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre es obligatorio.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no tiene un formato válido.');
        }
        if (strlen($contrasena_plana) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        if ($this->usuarioDao->existeEmail($email)) {
            throw new \InvalidArgumentException('Ya existe un usuario con ese email.');
        }

        $usuario = new Usuario($nombre, $email, $id_rol);
        $usuario->contrasena_hash = password_hash($contrasena_plana, PASSWORD_BCRYPT);

        $id = $this->usuarioDao->crear($usuario);
        $usuario->id_usuario = $id;

        return $usuario;
    }

    public function autenticar(string $email, string $contrasena_plana, string $direccion_ip): Usuario
    {
        $email = trim($email);

        $fallos = $this->intentosDao->contarFallosRecientes($email, self::VENTANA_BLOQUEO_MINUTOS);
        if ($fallos >= self::MAX_INTENTOS_FALLOS) {
            throw new \InvalidArgumentException('Cuenta temporalmente bloqueada por intentos fallidos. Intente más tarde.');
        }

        $usuario = $this->usuarioDao->obtenerPorEmail($email);

        if ($usuario === null || !password_verify($contrasena_plana, $usuario->contrasena_hash ?? '')) {
            $this->intentosDao->registrar($email, false, $direccion_ip);
            throw new \InvalidArgumentException('Credenciales inválidas.');
        }

        if (!$usuario->activo) {
            $this->intentosDao->registrar($email, false, $direccion_ip);
            throw new \InvalidArgumentException('La cuenta está desactivada.');
        }

        $this->intentosDao->registrar($email, true, $direccion_ip);

        return $usuario;
    }

    public static function cerrarSesion(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function estaAutenticado(): bool
    {
        return isset($_SESSION['id_usuario']) && is_numeric($_SESSION['id_usuario']);
    }

    public static function idUsuarioAutenticado(): int
    {
        if (!self::estaAutenticado()) {
            throw new \RuntimeException('No hay una sesión activa.');
        }

        return (int) $_SESSION['id_usuario'];
    }

    public static function rolUsuarioAutenticado(): int
    {
        if (!self::estaAutenticado()) {
            throw new \RuntimeException('No hay una sesión activa.');
        }

        return (int) ($_SESSION['id_rol'] ?? 0);
    }

    public static function esAdministrador(): bool
    {
        return self::estaAutenticado() && self::rolUsuarioAutenticado() === 4;
    }

    public static function esMesero(): bool
    {
        return self::estaAutenticado() && self::rolUsuarioAutenticado() === 2;
    }

    public static function esCliente(): bool
    {
        return self::estaAutenticado() && self::rolUsuarioAutenticado() === 1;
    }
}
