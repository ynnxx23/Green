<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;

class DetallePedidoDao
{
    public function listarPorPedido(int $id_pedido): array
    {
        $sql = 'SELECT dp.*, pr.nombre AS nombre_producto
                FROM detalle_pedidos dp
                JOIN productos pr ON dp.id_producto = pr.id_producto
                WHERE dp.id_pedido = :id_pedido';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->fetchAll();
    }
}
