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
    private const SESION_DURACION_SEGUNDOS = 14400; // 4 horas

    public function __construct(UsuarioDao $usuarioDao, IntentosLoginDao $intentosDao, RecuperarContrasenaDao $recuperarDao)
    {
        $this->usuarioDao = $usuarioDao;
        $this->intentosDao = $intentosDao;
        $this->recuperarDao = $recuperarDao;
    }

    // ========================================================================
    // REGISTRO
    // ========================================================================
    public function registrar(string $nombre, string $email, string $contrasena_plana, int $id_rol = 1): Usuario
    {
        $nombre = trim($nombre);
        $email = trim(strtolower($email));

        if ($nombre === '') {
            throw new \InvalidArgumentException('El nombre es obligatorio.');
        }
        if (strlen($nombre) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no tiene un formato válido.');
        }
        if (strlen($contrasena_plana) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }
        if (!preg_match('/[A-Z]/', $contrasena_plana)) {
            throw new \InvalidArgumentException('La contraseña debe incluir al menos una letra mayúscula.');
        }
        if (!preg_match('/[0-9]/', $contrasena_plana)) {
            throw new \InvalidArgumentException('La contraseña debe incluir al menos un número.');
        }

        if ($this->usuarioDao->existeEmail($email)) {
            throw new \InvalidArgumentException('Ya existe un usuario con ese email.');
        }

        $usuario = new Usuario($nombre, $email, $id_rol);
        $usuario->contrasena_hash = password_hash($contrasena_plana, PASSWORD_BCRYPT, ['cost' => 12]);

        $id = $this->usuarioDao->crear($usuario);
        $usuario->id_usuario = $id;

        return $usuario;
    }

    // ========================================================================
    // LOGIN (rehacer robusto)
    // ========================================================================
    public function autenticar(string $email, string $contrasena_plana, string $direccion_ip): Usuario
    {
        $email = trim(strtolower($email));

        // Verificar bloqueo por intentos fallidos
        $fallos = $this->intentosDao->contarFallosRecientes($email, self::VENTANA_BLOQUEO_MINUTOS);
        if ($fallos >= self::MAX_INTENTOS_FALLOS) {
            throw new \InvalidArgumentException('Cuenta temporalmente bloqueada por intentos fallidos. Intente más tarde.');
        }

        // Validación de formato de entrada
        if ($email === '') {
            throw new \InvalidArgumentException('El correo electrónico es obligatorio.');
        }
        if ($contrasena_plana === '') {
            throw new \InvalidArgumentException('La contraseña es obligatoria.');
        }

        $usuario = $this->usuarioDao->obtenerPorEmail($email);

        if ($usuario === null || !password_verify($contrasena_plana, $usuario->contrasena_hash ?? '')) {
            $this->intentosDao->registrar($email, false, $direccion_ip);
            throw new \InvalidArgumentException('Credenciales inválidas.');
        }

        if (!$usuario->activo) {
            $this->intentosDao->registrar($email, false, $direccion_ip);
            throw new \InvalidArgumentException('La cuenta está desactivada. Contacte al administrador.');
        }

        // Resetear intentos fallidos al autenticar correctamente
        $this->intentosDao->registrar($email, true, $direccion_ip);
        $this->intentosDao->limpiarIntentos($email);

        return $usuario;
    }

    // ========================================================================
    // SESIÓN (rehacer con configuración segura)
    // ========================================================================
    public static function iniciarSesion(array $usuario): void
    {
        // Configurar cookies de sesión seguras
        // Detectar HTTPS considerando proxies/load balancers (X-Forwarded-Proto)
        // que es el caso típico en Railway y otros proveedores de cloud.
        $esHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => self::SESION_DURACION_SEGUNDOS,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $esHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
        $_SESSION['id_rol'] = (int) $usuario['id_rol'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol_nombre'] = $usuario['rol_nombre'] ?? '';
        $_SESSION['activo'] = (bool) $usuario['activo'];
        $_SESSION['fecha_login'] = date('Y-m-d H:i:s');
        $_SESSION['ip_login'] = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    }

    public static function cerrarSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], true // httponly
            );
        }

        session_destroy();
    }

    public static function estaAutenticado(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

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

    public static function obtenerDatosSesion(): array
    {
        if (!self::estaAutenticado()) {
            return [];
        }

        return [
            'id_usuario' => $_SESSION['id_usuario'],
            'id_rol' => $_SESSION['id_rol'],
            'email' => $_SESSION['email'] ?? '',
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol_nombre' => $_SESSION['rol_nombre'] ?? '',
            'fecha_login' => $_SESSION['fecha_login'] ?? '',
        ];
    }

    public static function sesionExpirada(): bool
    {
        if (!self::estaAutenticado()) {
            return true;
        }

        $fechaLogin = $_SESSION['fecha_login'] ?? null;
        if ($fechaLogin === null) {
            return true;
        }

        $tiempoTranscurrido = time() - strtotime($fechaLogin);
        return $tiempoTranscurrido > self::SESION_DURACION_SEGUNDOS;
    }

    // ========================================================================
    // RECUPERACIÓN DE CONTRASEÑA
    // ========================================================================
    public function solicitarRecuperacion(string $email, string $direccion_ip): array
    {
        $email = trim(strtolower($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no tiene un formato válido.');
        }

        $usuario = $this->usuarioDao->obtenerPorEmail($email);

        // Siempre devolvemos éxito para no revelar si el email existe
        if ($usuario === null || !$usuario->activo) {
            return [
                'exito' => true,
                'mensaje' => 'Si el correo existe y está activo, se ha generado un token de recuperación.',
                'token' => null,
                'expira_en' => null,
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
        if (!preg_match('/[A-Z]/', $nueva_contrasena)) {
            throw new \InvalidArgumentException('La contraseña debe incluir al menos una letra mayúscula.');
        }
        if (!preg_match('/[0-9]/', $nueva_contrasena)) {
            throw new \InvalidArgumentException('La contraseña debe incluir al menos un número.');
        }

        $datos = $this->recuperarDao->obtenerTokenValido($token);
        if ($datos === null) {
            throw new \InvalidArgumentException('Token inválido o expirado.');
        }

        $this->usuarioDao->actualizarContraseña(
            (int) $datos['id_usuario'],
            password_hash($nueva_contrasena, PASSWORD_BCRYPT, ['cost' => 12])
        );

        $this->recuperarDao->marcarTokenUsado($token);
        $this->intentosDao->limpiarIntentos($this->usuarioDao->obtenerPorId((int) $datos['id_usuario'])->email ?? '');

        return [
            'exito' => true,
            'mensaje' => 'Contraseña restablecida correctamente.',
        ];
    }

    // ========================================================================
    // PERFIL
    // ========================================================================
    public function obtenerPerfil(int $id_usuario): ?array
    {
        $usuario = $this->usuarioDao->obtenerPorId($id_usuario);
        if ($usuario === null) {
            return null;
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'id_rol' => $usuario->id_rol,
            'rol_nombre' => $usuario->rol_nombre ?? '',
            'activo' => $usuario->activo,
            'fecha_creacion' => $usuario->fecha_creacion,
            'fecha_actualizacion' => $usuario->fecha_actualizacion,
            'avatar' => $usuario->avatar ?? null,
            'telefono' => $usuario->telefono ?? null,
            'fecha_nacimiento' => $usuario->fecha_nacimiento ?? null,
        ];
    }

    public function actualizarPerfil(int $id_usuario, array $datos): array
    {
        $usuario = $this->usuarioDao->obtenerPorId($id_usuario);
        if ($usuario === null) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $camposActualizar = [];

        if (isset($datos['nombre']) && trim($datos['nombre']) !== '') {
            $camposActualizar['nombre'] = trim($datos['nombre']);
        }

        if (isset($datos['email']) && trim($datos['email']) !== '') {
            $email = trim(strtolower($datos['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('El email no tiene un formato válido.');
            }
            if ($email !== $usuario->email && $this->usuarioDao->existeEmail($email)) {
                throw new \InvalidArgumentException('Ya existe un usuario con ese email.');
            }
            $camposActualizar['email'] = $email;
        }

        if (isset($datos['contrasena']) && trim($datos['contrasena']) !== '') {
            if (strlen($datos['contrasena']) < 8) {
                throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
            }
            $camposActualizar['contrasena_hash'] = password_hash($datos['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (isset($datos['telefono'])) {
            $camposActualizar['telefono'] = $datos['telefono'] === '' ? null : $datos['telefono'];
        }

        if (isset($datos['fecha_nacimiento'])) {
            $camposActualizar['fecha_nacimiento'] = $datos['fecha_nacimiento'] === '' ? null : $datos['fecha_nacimiento'];
        }

        if (isset($datos['avatar'])) {
            $camposActualizar['avatar'] = $datos['avatar'] === '' ? null : $datos['avatar'];
        }

        if (empty($camposActualizar)) {
            throw new \InvalidArgumentException('No hay campos para actualizar.');
        }

        $camposActualizar['fecha_actualizacion'] = date('Y-m-d H:i:s');

        $this->usuarioDao->actualizar($id_usuario, $camposActualizar);

        return [
            'exito' => true,
            'mensaje' => 'Perfil actualizado correctamente.',
        ];
    }
}
