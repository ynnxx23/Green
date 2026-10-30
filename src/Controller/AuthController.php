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

    public function __construct(
        ServicioDeAutenticacion $auth,
        AuthMiddleware $authMiddleware,
        RateLimiterMiddleware $rateLimiter
    ) {
        $this->auth = $auth;
        $this->authMiddleware = $authMiddleware;
        $this->rateLimiter = $rateLimiter;
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

            session_start();
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario->id_usuario;
            $_SESSION['id_rol'] = $usuario->id_rol;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['nombre'] = $usuario->nombre;

            return [
                'exito' => true,
                'datos' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'id_rol' => $usuario->id_rol,
                ],
                'mensaje' => 'Inicio de sesión exitoso.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    private function logout(): array
    {
        session_start();
        $this->auth->cerrarSesion();

        return [
            'exito' => true,
            'mensaje' => 'Sesión cerrada correctamente.',
        ];
    }

    private function verificarSesion(): array
    {
        session_start();

        if (!ServicioDeAutenticacion::estaAutenticado()) {
            return $this->error('No hay sesión activa.', 401);
        }

        return [
            'exito' => true,
            'datos' => [
                'id_usuario' => ServicioDeAutenticacion::idUsuarioAutenticado(),
                'id_rol' => ServicioDeAutenticacion::rolUsuarioAutenticado(),
                'es_administrador' => ServicioDeAutenticacion::esAdministrador(),
                'es_mesero' => ServicioDeAutenticacion::esMesero(),
                'es_cocinero' => ServicioDeAutenticacion::esCocinero(),
                'es_cliente' => ServicioDeAutenticacion::esCliente(),
            ],
            'mensaje' => 'Sesión activa.',
        ];
    }

    private function recuperar(): array
    {
        $entrada = $this->leerCuerpoJson();
        $email = $entrada['email'] ?? '';

        if ($email === '') {
            return $this->error('El correo electrónico es obligatorio.', 400);
        }

        $direccion_ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

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
        $cuerpo = file_get_contents('php://input');
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
