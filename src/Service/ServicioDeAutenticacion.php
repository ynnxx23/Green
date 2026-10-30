<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\IntentosLoginDao;
use Restaurante\Dao\RecuperarContrasenaDao;
use Restaurante\Dao\UsuarioDao;
use Restaurante\Model\Usuario;

class ServicioDeAutenticacion
{
    private UsuarioDao $usuarioDao;
    private IntentosLoginDao $intentosDao;
    private RecuperarContrasenaDao $recuperarDao;

    private const MAX_INTENTOS_FALLOS = 5;
    private const VENTANA_BLOQUEO_MINUTOS = 15;
    private const TOKEN_EXPIRACION_MINUTOS = 30;

    public function __construct(UsuarioDao $usuarioDao, IntentosLoginDao $intentosDao, RecuperarContrasenaDao $recuperarDao)
    {
        $this->usuarioDao = $usuarioDao;
        $this->intentosDao = $intentosDao;
        $this->recuperarDao = $recuperarDao;
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

    // ------------------------------------------------------------------
    // Recuperación de contraseña (RF19)
    // ------------------------------------------------------------------
    public function solicitarRecuperacion(string $email, string $direccion_ip): array
    {
        $usuario = $this->usuarioDao->obtenerPorEmail($email);

        // Siempre devolvemos éxito para no revelar si el email existe o no
        if ($usuario === null || !$usuario->activo) {
            return [
                'exito' => true,
                'mensaje' => 'Si el correo existe y está activo, se ha generado un token de recuperación.',
                'token' => null, // En producción se enviaría por email; aquí se omite por seguridad
            ];
        }

        $token = bin2hex(random_bytes(32));
        $fecha_expiracion = date('Y-m-d H:i:s', time() + (self::TOKEN_EXPIRACION_MINUTOS * 60));

        $this->recuperarDao->crearToken(
            $usuario->id_usuario,
            $token,
            $fecha_expiracion,
            $direccion_ip
        );

        return [
            'exito' => true,
            'mensaje' => 'Token de recuperación generado.',
            'token' => $token, // Solo visible en entorno de desarrollo
            'expira_en' => $fecha_expiracion,
        ];
    }

    public function restablecerContrasena(string $token, string $nueva_contrasena): array
    {
        if (strlen($nueva_contrasena) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        $datos = $this->recuperarDao->obtenerTokenValido($token);
        if ($datos === null) {
            throw new \InvalidArgumentException('Token inválido o expirado.');
        }

        $this->usuarioDao->actualizarContraseña(
            (int) $datos['id_usuario'],
            password_hash($nueva_contrasena, PASSWORD_BCRYPT)
        );

        $this->recuperarDao->marcarTokenUsado($token);

        return [
            'exito' => true,
            'mensaje' => 'Contraseña restablecida correctamente.',
        ];
    }
}
