<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Pedido
{
    public ?int $id_pedido = null;
    public int $id_mesa;
    public int $id_mesero;
    public ?string $fecha_pedido = null;
    public string $estado = 'pendiente';
    public float $total = 0.0;
    public ?string $fecha_actualizacion = null;

    public function __construct(int $id_mesa, int $id_mesero)
    {
        $this->id_mesa = $id_mesa;
        $this->id_mesero = $id_mesero;
    }

    public function sePuedeModificar(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_preparacion'], true);
    }

    public function esTerminal(): bool
    {
        return in_array($this->estado, ['entregado', 'cancelado'], true);
    }
}
