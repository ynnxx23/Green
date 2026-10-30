<?php

declare(strict_types=1);

namespace Restaurante\Model;

class DetallePedido
{
    public ?int $id_detalle = null;
    public int $id_pedido;
    public int $id_producto;
    public int $cantidad = 1;
    public float $precio_unitario = 0.0;

    public function __construct(int $id_pedido, int $id_producto, int $cantidad, float $precio_unitario)
    {
        $this->id_pedido = $id_pedido;
        $this->id_producto = $id_producto;
        $this->cantidad = $cantidad;
        $this->precio_unitario = $precio_unitario;
    }

    public function subtotal(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
