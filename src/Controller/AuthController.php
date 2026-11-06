<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Dao\UsuarioDao;
use Restaurante\Dao\IntentosLoginDao;
use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Middleware\RateLimiterMiddleware;
use Restaurante\Service\ServicioDeAutenticacion;

class AuthController
{
    private ServicioDeAutenticacion $auth;
    private AuthMiddleware $authMiddleware;
    private RateLimiterMiddleware $rateLimiter;
    private UsuarioDao $usuarioDao;

    public function __construct(
        ServicioDeAutenticacion $auth,
        AuthMiddleware $authMiddleware,
        RateLimiterMiddleware $rateLimiter,
        UsuarioDao $usuarioDao
    ) {
        $this->auth = $auth;
        $this->authMiddleware = $authMiddleware;
        $this->rateLimiter = $rateLimiter;
        $this->usuarioDao = $usuarioDao;
    }

    public function manejar(string $ruta_interna, string $metodo): array
    {
        switch ($ruta_interna) {
            case 'registro':
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->registro();

            case 'login':
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->login();

            case 'logout':
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->logout();

            case 'sesion':
                if ($metodo !== 'GET') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->verificarSesion();

            case 'recuperar':
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->recuperar();

            case 'restablecer':
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->restablecer();

            case 'perfil':
                if ($metodo === 'GET') {
                    return $this->perfil();
                }
                if ($metodo === 'PUT') {
                    return $this->actualizarPerfil();
                }
                return $this->error('Método no permitido.', 405);

            case 'cambiar-contrasena':
                if ($metodo !== 'PUT') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->cambiarContrasena();

            default:
                return $this->error('Endpoint no encontrado.', 404);
        }
    }

    private function registro(): array
    {
        $entrada = $this->leerCuerpoJson();

        $nombre = $entrada['nombre'] ?? '';
        $email = $entrada['email'] ?? '';
        $contrasena = $entrada['contrasena'] ?? '';
        $id_rol = (int) ($entrada['id_rol'] ?? 1);

        // Solo clientes o encargados pueden registrarse públicamente
        if (!in_array($id_rol, [1, 2], true)) {
            $id_rol = 1;
        }

        try {
            $usuario = $this->auth->registrar($nombre, $email, $contrasena, $id_rol);

            return [
                'exito' => true,
                'datos' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'id_rol' => $usuario->id_rol,
                ],
                'mensaje' => 'Usuario registrado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al registrar el usuario.', 500);
        }
    }

    private function login(): array
    {
        $entrada = $this->leerCuerpoJson();

        $email = $entrada['email'] ?? '';
        $contrasena = $entrada['contrasena'] ?? '';
        $direccion_ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

        try {
            $usuario = $this->auth->autenticar($email, $contrasena, $direccion_ip);

            // Iniciar sesión segura
            ServicioDeAutenticacion::iniciarSesion([
                'id_usuario' => $usuario->id_usuario,
                'id_rol' => $usuario->id_rol,
                'email' => $usuario->email,
                'nombre' => $usuario->nombre,
                'rol_nombre' => $usuario->rol_nombre ?? '',
                'activo' => $usuario->activo,
            ]);

            return [
                'exito' => true,
                'datos' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'id_rol' => $usuario->id_rol,
                    'rol_nombre' => $usuario->rol_nombre ?? '',
                ],
                'mensaje' => 'Inicio de sesión exitoso.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Throwable $e) {
            return $this->error('Error interno al iniciar sesión: ' . $e->getMessage(), 500);
        }
    }

    private function logout(): array
    {
        if (!ServicioDeAutenticacion::estaAutenticado()) {
            return $this->error('No hay sesión activa.', 401);
        }

        ServicioDeAutenticacion::cerrarSesion();

        return [
            'exito' => true,
            'mensaje' => 'Sesión cerrada correctamente.',
        ];
    }

    private function verificarSesion(): array
    {
        if (!ServicioDeAutenticacion::estaAutenticado()) {
            return $this->error('No hay sesión activa.', 401);
        }

        if (ServicioDeAutenticacion::sesionExpirada()) {
            ServicioDeAutenticacion::cerrarSesion();
            return $this->error('La sesión ha expirado. Inicie sesión nuevamente.', 401);
        }

        return [
            'exito' => true,
            'datos' => ServicioDeAutenticacion::obtenerDatosSesion(),
            'mensaje' => 'Sesión activa.',
        ];
    }

    private function perfil(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
        $perfil = $this->auth->obtenerPerfil($id_usuario);

        if ($perfil === null) {
            return $this->error('Usuario no encontrado.', 404);
        }

        return [
            'exito' => true,
            'datos' => $perfil,
            'mensaje' => 'Perfil obtenido correctamente.',
        ];
    }

    private function actualizarPerfil(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
        $entrada = $this->leerCuerpoJson();

        try {
            $resultado = $this->auth->actualizarPerfil($id_usuario, $entrada);

            return [
                'exito' => true,
                'mensaje' => $resultado['mensaje'],
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar el perfil.', 500);
        }
    }

    private function cambiarContrasena(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = ServicioDeAutenticacion::idUsuarioAutenticado();
        $entrada = $this->leerCuerpoJson();

        $contrasena_actual = $entrada['contrasena_actual'] ?? '';
        $nueva_contrasena = $entrada['nueva_contrasena'] ?? '';

        if ($contrasena_actual === '' || $nueva_contrasena === '') {
            return $this->error('Se requieren la contraseña actual y la nueva.', 400);
        }

        $usuario = $this->usuarioDao->obtenerPorId($id_usuario);
        if ($usuario === null || !password_verify($contrasena_actual, $usuario->contrasena_hash ?? '')) {
            return $this->error('La contraseña actual es incorrecta.', 401);
        }

        try {
            $this->auth->actualizarPerfil($id_usuario, ['contrasena' => $nueva_contrasena]);

            return [
                'exito' => true,
                'mensaje' => 'Contraseña cambiada correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function recuperar(): array
    {
        $entrada = $this->leerCuerpoJson();
        $email = $entrada['email'] ?? '';
        $direccion_ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

        if ($email === '') {
            return $this->error('El correo electrónico es obligatorio.', 400);
        }

        // Rate limiting para recuperación
        $clienteId = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
        $this->rateLimiter->consumir($clienteId, 'recuperar:' . $direccion_ip);

        try {
            $resultado = $this->auth->solicitarRecuperacion($email, $direccion_ip);

            return [
                'exito' => true,
                'datos' => [
                    'token' => $resultado['token'] ?? null,
                    'expira_en' => $resultado['expira_en'] ?? null,
                ],
                'mensaje' => $resultado['mensaje'],
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function restablecer(): array
    {
        $entrada = $this->leerCuerpoJson();
        $token = $entrada['token'] ?? '';
        $nueva_contrasena = $entrada['nueva_contrasena'] ?? '';

        if ($token === '' || $nueva_contrasena === '') {
            return $this->error('Se requieren token y nueva contraseña.', 400);
        }

        try {
            $resultado = $this->auth->restablecerContrasena($token, $nueva_contrasena);

            return [
                'exito' => true,
                'mensaje' => $resultado['mensaje'],
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function leerCuerpoJson(): array
    {
        $cuerpo = @file_get_contents('php://input');
        if ($cuerpo === false || empty($cuerpo)) {
            $handle = @fopen('php://input', 'r');
            if ($handle) {
                $cuerpo = '';
                while (!feof($handle)) {
                    $fragmento = fread($handle, 8192);
                    if ($fragmento === false) {
                        break;
                    }
                    $cuerpo .= $fragmento;
                }
                fclose($handle);
            }
        }
        if (empty($cuerpo) && isset($_SERVER['RAW_INPUT'])) {
            $cuerpo = $_SERVER['RAW_INPUT'];
        }
        // Eliminar BOM UTF-8 si está presente
        if (substr($cuerpo, 0, 3) === "\xEF\xBB\xBF") {
            $cuerpo = substr($cuerpo, 3);
        }

        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
    }

    private function error(string $mensaje, int $codigo_http): array
    {
        http_response_code($codigo_http);

        return [
            'exito' => false,
            'datos' => null,
            'mensaje' => $mensaje,
            'codigo' => $codigo_http,
        ];
    }
}
