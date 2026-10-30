<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;

class RecuperarContrasenaDao
{
    public function crearToken(int $id_usuario, string $token, string $fecha_expiracion, ?string $direccion_ip): void
    {
        // Invalidar tokens previos no usados de este usuario
        $sql = 'UPDATE recuperar_contrasena SET usado = 1 WHERE id_usuario = :id_usuario AND usado = 0';
        Conexion::obtener()->prepare($sql)->execute([':id_usuario' => $id_usuario]);

        $sql = 'INSERT INTO recuperar_contrasena (id_usuario, token, fecha_expiracion, direccion_ip)
                VALUES (:id_usuario, :token, :fecha_expiracion, :ip)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':token' => $token,
            ':fecha_expiracion' => $fecha_expiracion,
            ':ip' => $direccion_ip,
        ]);
    }

    public function obtenerTokenValido(string $token): ?array
    {
        $sql = 'SELECT rc.*, u.email, u.nombre, u.id_rol
                FROM recuperar_contrasena rc
                JOIN usuarios u ON u.id_usuario = rc.id_usuario
                WHERE rc.token = :token
                  AND rc.usado = 0
                  AND rc.fecha_expiracion > NOW()';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':token' => $token]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function marcarTokenUsado(string $token): void
    {
        $sql = 'UPDATE recuperar_contrasena SET usado = 1 WHERE token = :token';
        Conexion::obtener()->prepare($sql)->execute([':token' => $token]);
    }
}
