<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\Reserva;

class ReservaDao
{
    public function crearConBloqueo(Reserva $reserva): int
    {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('
                SELECT id_reserva
                FROM reservas
                WHERE id_mesa = :id_mesa
                  AND fecha_reserva = :fecha_reserva
                  AND estado IN (\'pendiente\', \'confirmada\')
                FOR UPDATE
            ');
            $stmt->execute([
                ':id_mesa' => $reserva->id_mesa,
                ':fecha_reserva' => $reserva->fecha_reserva,
            ]);
            $stmt->fetchAll();

            $sql = 'INSERT INTO reservas
                        (id_mesa, id_usuario, fecha_reserva, hora_inicio, hora_fin, estado, fecha_creacion)
                    VALUES (:id_mesa, :id_usuario, :fecha_reserva, :hora_inicio, :hora_fin, :estado, CURRENT_TIMESTAMP)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_mesa' => $reserva->id_mesa,
                ':id_usuario' => $reserva->id_usuario,
                ':fecha_reserva' => $reserva->fecha_reserva,
                ':hora_inicio' => $reserva->hora_inicio,
                ':hora_fin' => $reserva->hora_fin,
                ':estado' => $reserva->estado,
            ]);

            $id = (int) $pdo->lastInsertId();
            $pdo->commit();

            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId(int $id_reserva): ?Reserva
    {
        $sql = 'SELECT * FROM reservas WHERE id_reserva = :id_reserva';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_reserva' => $id_reserva]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearReserva($fila);
    }

    public function listarPorUsuario(int $id_usuario): array
    {
        $sql = 'SELECT * FROM reservas
                WHERE id_usuario = :id_usuario
                ORDER BY fecha_reserva DESC, hora_inicio DESC';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $filas = $stmt->fetchAll();

        $reservas = [];
        foreach ($filas as $fila) {
            $reservas[] = $this->mapearReserva($fila);
        }

        return $reservas;
    }

    public function listarTodas(): array
    {
        $sql = 'SELECT r.*, u.nombre AS nombre_usuario, u.email AS email_usuario,
                       m.numero_mesa AS numero_mesa
                FROM reservas r
                JOIN usuarios u ON r.id_usuario = u.id_usuario
                JOIN mesas m ON r.id_mesa = m.id_mesa
                ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $reservas = [];
        foreach ($filas as $fila) {
            $reserva = $this->mapearReserva($fila);
            $reserva->nombre_usuario = $fila['nombre_usuario'] ?? null;
            $reserva->email_usuario = $fila['email_usuario'] ?? null;
            $reserva->numero_mesa = $fila['numero_mesa'] ?? null;
            $reservas[] = $reserva;
        }

        return $reservas;
    }

    public function cancelar(int $id_reserva): bool
    {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('
                SELECT id_reserva, estado
                FROM reservas
                WHERE id_reserva = :id_reserva
                FOR UPDATE
            ');
            $stmt->execute([':id_reserva' => $id_reserva]);
            $fila = $stmt->fetch();

            if ($fila === false) {
                $pdo->rollBack();
                return false;
            }

            if ($fila['estado'] !== 'pendiente' && $fila['estado'] !== 'confirmada') {
                $pdo->rollBack();
                return false;
            }

            $sql = 'UPDATE reservas
                        SET estado = \'cancelada\', fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id_reserva = :id_reserva';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_reserva' => $id_reserva]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function cambiarEstado(int $id_reserva, string $estado): bool
    {
        $estadosValidos = ['pendiente', 'confirmada', 'cancelada', 'vencida', 'completada'];
        if (!in_array($estado, $estadosValidos, true)) {
            return false;
        }

        $pdo = Conexion::obtener();
        $sql = 'UPDATE reservas
                    SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_reserva = :id_reserva';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':estado' => $estado,
            ':id_reserva' => $id_reserva,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function existeSolapamiento(
        int $id_mesa,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin,
        ?int $id_reserva_excluir = null
    ): bool {
        $sql = 'SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_mesa = :id_mesa
                  AND fecha_reserva = :fecha_reserva
                  AND estado IN (\'pendiente\', \'confirmada\')
                  AND hora_inicio < :hora_fin
                  AND hora_fin > :hora_inicio';
        $params = [
            ':id_mesa' => $id_mesa,
            ':fecha_reserva' => $fecha_reserva,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
        ];

        if ($id_reserva_excluir !== null) {
            $sql .= ' AND id_reserva <> :id_reserva_excluir';
            $params[':id_reserva_excluir'] = $id_reserva_excluir;
        }

        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch();

        return (int) $fila['total'] > 0;
    }

    private function mapearReserva(array $fila): Reserva
    {
        $reserva = new Reserva(
            (int) $fila['id_mesa'],
            (int) $fila['id_usuario'],
            $fila['fecha_reserva'],
            $fila['hora_inicio'],
            $fila['hora_fin'],
            $fila['estado']
        );
        $reserva->id_reserva = (int) $fila['id_reserva'];
        $reserva->fecha_creacion = $fila['fecha_creacion'] ?? null;
        $reserva->fecha_actualizacion = $fila['fecha_actualizacion'] ?? null;

        return $reserva;
    }
}
