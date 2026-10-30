<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Mesa
{
    public ?int $id_mesa = null;
    public int $id_area;
    public string $numero_mesa;
    public int $capacidad = 1;
    public bool $activo = true;
    public ?string $fecha_creacion = null;

    public function __construct(int $id_area, string $numero_mesa)
    {
        $this->id_area = $id_area;
        $this->numero_mesa = $numero_mesa;
    }
}
