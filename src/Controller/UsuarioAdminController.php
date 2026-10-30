<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Dao\UsuarioDao;
use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeAutenticacion;

class UsuarioAdminController
{
    private UsuarioDao $usuarioDao;
    private AuthMiddleware $authMiddleware;
    private ServicioDeAutenticacion $auth;

    public function __construct(UsuarioDao $usuarioDao, AuthMiddleware $authMiddleware, ServicioDeAutenticacion $auth)
    {
        $this->usuarioDao = $usuarioDao;
        $this->authMiddleware = $authMiddleware;
        $this->auth = $auth;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        $this->authMiddleware->requiereAdministrador();

        if (preg_match('/^(\d+)$/', $ruta_interna, $coincidencias)) {
            $id_usuario = (int) $coincidencias[1];

            if ($metodo === 'PUT') {
                return $this->editarUsuario($id_usuario);
            }

            if ($metodo === 'DELETE') {
                return $this->desactivarUsuario($id_usuario);
            }

            return $this->error('Método no permitido.', 405);
        }

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo === 'GET') {
                return $this->listarUsuarios();
            }

            if ($metodo === 'POST') {
                return $this->crearUsuario();
            }

            return $this->error('Método no permitido.', 405);
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listarUsuarios(): array
    {
        $usuarios = $this->usuarioDao->listarTodos();

        $datos = [];
        foreach ($usuarios as $usuario) {
            $datos[] = [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'id_rol' => $usuario->id_rol,
                'activo' => $usuario->activo,
                'fecha_creacion' => $usuario->fecha_creacion,
                'fecha_actualizacion' => $usuario->fecha_actualizacion,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Usuarios obtenidos correctamente.',
        ];
    }

    private function crearUsuario(): array
    {
        $entrada = $this->leerCuerpoJson();

        $nombre = $entrada['nombre'] ?? '';
        $email = $entrada['email'] ?? '';
        $contrasena = $entrada['contrasena'] ?? '';
        $id_rol = (int) ($entrada['id_rol'] ?? 1);

        if ($nombre === '' || $email === '' || $contrasena === '') {
            return $this->error('Faltan datos obligatorios: nombre, email, contraseña.', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('El email no tiene un formato válido.', 400);
        }

        if (strlen($contrasena) < 8) {
            return $this->error('La contraseña debe tener al menos 8 caracteres.', 400);
        }

        try {
            $usuario = $this->auth->registrar($nombre, $email, $contrasena, $id_rol);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }

        return [
            'exito' => true,
            'datos' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'id_rol' => $usuario->id_rol,
            ],
            'mensaje' => 'Usuario creado correctamente.',
        ];
    }

    private function editarUsuario(int $id_usuario): array
    {
        $entrada = $this->leerCuerpoJson();

        $nombre = $entrada['nombre'] ?? null;
        $email = $entrada['email'] ?? null;
        $id_rol = isset($entrada['id_rol']) ? (int) $entrada['id_rol'] : null;
        $activo = isset($entrada['activo']) ? (bool) $entrada['activo'] : null;

        if ($nombre === null && $email === null && $id_rol === null && $activo === null) {
            return $this->error('No hay campos para actualizar.', 400);
        }

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('El email no tiene un formato válido.', 400);
        }

        $usuarioExistente = $this->usuarioDao->obtenerPorId($id_usuario);
        if ($usuarioExistente === null) {
            return $this->error('El usuario no existe.', 404);
        }

        if ($email !== null && $email !== $usuarioExistente->email && $this->usuarioDao->existeEmail($email)) {
            return $this->error('Ya existe un usuario con ese email.', 400);
        }

        try {
            $this->actualizarCamposUsuario($id_usuario, $nombre, $email, $id_rol, $activo);
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar el usuario.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Usuario actualizado correctamente.',
        ];
    }

    private function desactivarUsuario(int $id_usuario): array
    {
        $usuario = $this->usuarioDao->obtenerPorId($id_usuario);
        if ($usuario === null) {
            return $this->error('El usuario no existe.', 404);
        }

        if ($usuario->id_rol === 4) {
            $administradores = array_filter(
                $this->usuarioDao->listarTodos(),
                fn($u) => $u->id_rol === 4 && $u->activo && $u->id_usuario !== $id_usuario
            );
            if (count($administradores) === 0) {
                return $this->error('No se puede desactivar el único administrador activo.', 400);
            }
        }

        try {
            $this->usuarioDao->desactivar($id_usuario);
        } catch (\PDOException $e) {
            return $this->error('Error al desactivar el usuario.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Usuario desactivado correctamente.',
        ];
    }

    private function actualizarCamposUsuario(
        int $id_usuario,
        ?string $nombre,
        ?string $email,
        ?int $id_rol,
        ?bool $activo
    ): void {
        $campos = [];
        $params = [];

        if ($nombre !== null) {
            $campos[] = 'nombre = :nombre';
            $params[':nombre'] = $nombre;
        }
        if ($email !== null) {
            $campos[] = 'email = :email';
            $params[':email'] = $email;
        }
        if ($id_rol !== null) {
            $campos[] = 'id_rol = :id_rol';
            $params[':id_rol'] = $id_rol;
        }
        if ($activo !== null) {
            $campos[] = 'activo = :activo';
            $params[':activo'] = $activo ? 1 : 0;
        }

        $campos[] = 'fecha_actualizacion = CURRENT_TIMESTAMP';
        $params[':id_usuario'] = $id_usuario;

        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id_usuario = :id_usuario';
        $stmt = \Restaurante\Database\Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
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
