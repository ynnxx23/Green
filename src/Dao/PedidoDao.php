<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;

class PedidoDao
{
    public function crearConDetalles(int $id_mesa, int $id_mesero, array $detalles): int
    {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();

        try {
            $total = 0.0;
            foreach ($detalles as $detalle) {
                $total += (float) $detalle['cantidad'] * (float) $detalle['precio_unitario'];
            }

            $stmt = $pdo->prepare('
                INSERT INTO pedidos (id_mesa, id_mesero, fecha_pedido, estado, total, fecha_actualizacion)
                VALUES (:id_mesa, :id_mesero, CURRENT_TIMESTAMP, \'pendiente\', :total, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([
                ':id_mesa' => $id_mesa,
                ':id_mesero' => $id_mesero,
                ':total' => $total,
            ]);
            $id_pedido = (int) $pdo->lastInsertId();

            $stmtDetalle = $pdo->prepare('
                INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
                VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)
            ');
            foreach ($detalles as $detalle) {
                $stmtDetalle->execute([
                    ':id_pedido' => $id_pedido,
                    ':id_producto' => $detalle['id_producto'],
                    ':cantidad' => $detalle['cantidad'],
                    ':precio_unitario' => $detalle['precio_unitario'],
                ]);
            }

            $pdo->commit();
            return $id_pedido;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId(int $id_pedido): ?array
    {
        $pdo = Conexion::obtener();

        $stmt = $pdo->prepare('
            SELECT p.*, u.nombre AS nombre_mesero, m.numero_mesa
            FROM pedidos p
            JOIN usuarios u ON p.id_mesero = u.id_usuario
            JOIN mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_pedido = :id_pedido
        ');
        $stmt->execute([':id_pedido' => $id_pedido]);
        $pedido = $stmt->fetch();

        if ($pedido === false) {
            return null;
        }

        $stmtDet = $pdo->prepare('
            SELECT dp.*, pr.nombre AS nombre_producto
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.id_producto = pr.id_producto
            WHERE dp.id_pedido = :id_pedido
        ');
        $stmtDet->execute([':id_pedido' => $id_pedido]);
        $detalles = $stmtDet->fetchAll();

        return [
            'pedido' => $pedido,
            'detalles' => $detalles,
        ];
    }

    public function listarPorMesero(int $id_mesero): array
    {
        $sql = 'SELECT p.*, m.numero_mesa
                FROM pedidos p
                JOIN mesas m ON p.id_mesa = m.id_mesa
                WHERE p.id_mesero = :id_mesero
                ORDER BY p.fecha_pedido DESC';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_mesero' => $id_mesero]);
        return $stmt->fetchAll();
    }

    public function listarTodos(): array
    {
        $sql = 'SELECT p.*, u.nombre AS nombre_mesero, m.numero_mesa
                FROM pedidos p
                JOIN usuarios u ON p.id_mesero = u.id_usuario
                JOIN mesas m ON p.id_mesa = m.id_mesa
                ORDER BY p.fecha_pedido DESC';
        $stmt = Conexion::obtener()->query($sql);
        return $stmt->fetchAll();
    }

    public function listarPorEstado(string $estado): array
    {
        $sql = 'SELECT p.*, u.nombre AS nombre_mesero, m.numero_mesa
                FROM pedidos p
                JOIN usuarios u ON p.id_mesero = u.id_usuario
                JOIN mesas m ON p.id_mesa = m.id_mesa
                WHERE p.estado = :estado
                ORDER BY p.fecha_pedido ASC';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll();
    }

    public function cambiarEstado(int $id_pedido, string $estado): bool
    {
        $estadosValidos = ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
        if (!in_array($estado, $estadosValidos, true)) {
            return false;
        }

        $pdo = Conexion::obtener();
        $sql = 'UPDATE pedidos
                    SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_pedido = :id_pedido';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':estado' => $estado,
            ':id_pedido' => $id_pedido,
        ]);

        return $stmt->rowCount() > 0;
    }
}
