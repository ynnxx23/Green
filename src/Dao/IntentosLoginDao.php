<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;

class IntentosLoginDao
{
    public function registrar(string $email, bool $exitoso, ?string $direccion_ip): void
    {
        $sql = 'INSERT INTO intentos_login (email, exitoso, fecha_intento, direccion_ip)
                VALUES (:email, :exitoso, CURRENT_TIMESTAMP, :direccion_ip)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':exitoso' => $exitoso ? 1 : 0,
            ':direccion_ip' => $direccion_ip,
        ]);
    }

    public function contarFallosRecientes(string $email, int $minutos): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM intentos_login
                WHERE email = :email
                  AND exitoso = 0
                  AND fecha_intento >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':minutos' => $minutos,
        ]);
        $fila = $stmt->fetch();

        return (int) $fila['total'];
    }

    public function ultimoExito(string $email): ?string
    {
        $sql = 'SELECT fecha_intento
                FROM intentos_login
                WHERE email = :email
                  AND exitoso = 1
                ORDER BY fecha_intento DESC
                LIMIT 1';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila['fecha_intento'];
    }
}
