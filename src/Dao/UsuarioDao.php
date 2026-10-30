<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use PDO;
use Restaurante\Database\Conexion;
use Restaurante\Model\Usuario;

class UsuarioDao
{
    public function obtenerPorId(int $id_usuario): ?Usuario
    {
        $sql = 'SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = :id_usuario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearUsuario($fila);
    }

    public function obtenerPorEmail(string $email): ?Usuario
    {
        $sql = 'SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.email = :email';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearUsuario($fila);
    }

    public function existeEmail(string $email): bool
    {
        $sql = 'SELECT COUNT(*) AS total FROM usuarios WHERE email = :email';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $fila = $stmt->fetch();

        return (int) $fila['total'] > 0;
    }

    public function crear(Usuario $usuario): int
    {
        $sql = 'INSERT INTO usuarios (nombre, email, contrasena_hash, id_rol, activo, fecha_creacion)
                VALUES (:nombre, :email, :contrasena_hash, :id_rol, :activo, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $usuario->nombre,
            ':email' => $usuario->email,
            ':contrasena_hash' => $usuario->contrasena_hash,
            ':id_rol' => $usuario->id_rol,
            ':activo' => $usuario->activo ? 1 : 0,
        ]);

        return (int) Conexion::obtener()->lastInsertId();
    }

    public function actualizarContrasena(int $id_usuario, string $contrasena_hash): void
    {
        $sql = 'UPDATE usuarios SET contrasena_hash = :contrasena_hash, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':contrasena_hash' => $contrasena_hash,
            ':id_usuario' => $id_usuario,
        ]);
    }

    public function desactivar(int $id_usuario): void
    {
        $sql = 'UPDATE usuarios SET activo = 0, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    public function activar(int $id_usuario): void
    {
        $sql = 'UPDATE usuarios SET activo = 1, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    public function listarTodos(): array
    {
        $sql = 'SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.nombre';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $usuarios = [];
        foreach ($filas as $fila) {
            $usuarios[] = $this->mapearUsuario($fila);
        }

        return $usuarios;
    }

    private function mapearUsuario(array $fila): Usuario
    {
        $usuario = new Usuario($fila['nombre'], $fila['email'], (int) $fila['id_rol']);
        $usuario->id_usuario = (int) $fila['id_usuario'];
        $usuario->contrasena_hash = $fila['contrasena_hash'] ?? null;
        $usuario->activo = (bool) $fila['activo'];
        $usuario->fecha_creacion = $fila['fecha_creacion'] ?? null;
        $usuario->fecha_actualizacion = $fila['fecha_actualizacion'] ?? null;

        return $usuario;
    }
}
