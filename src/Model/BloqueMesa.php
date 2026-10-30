<?php

declare(strict_types=1);

namespace Restaurante\Model;

class BloqueMesa
{
    public ?int $id_bloque = null;
    public int $id_mesa;
    public string $fecha_inicio;
    public string $fecha_fin;
    public ?string $motivo = null;
    public ?int $creado_por = null;
    public ?string $fecha_creacion = null;

    public function __construct(int $id_mesa, string $fecha_inicio, string $fecha_fin, ?string $motivo = null)
    {
        $this->id_mesa = $id_mesa;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->motivo = $motivo;
    }
}
