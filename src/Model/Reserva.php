<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Reserva
{
    public ?int $id_reserva = null;
    public int $id_mesa;
    public int $id_usuario;
    public string $fecha_reserva;
    public string $hora_inicio;
    public string $hora_fin;
    public string $estado = 'pendiente';
    public ?string $fecha_creacion = null;
    public ?string $fecha_actualizacion = null;

    public function __construct(
        int $id_mesa,
        int $id_usuario,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin,
        string $estado = 'pendiente'
    ) {
        $this->id_mesa = $id_mesa;
        $this->id_usuario = $id_usuario;
        $this->fecha_reserva = $fecha_reserva;
        $this->hora_inicio = $hora_inicio;
        $this->hora_fin = $hora_fin;
        $this->estado = $estado;
    }

    public function estaActiva(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada'], true);
    }

    public function sePuedeCancelar(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada'], true);
    }
}
