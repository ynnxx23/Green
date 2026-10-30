<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Area
{
    public ?int $id_area = null;
    public string $nombre;
    public ?string $descripcion = null;
    public bool $activo = true;
    public ?string $fecha_creacion = null;

    public function __construct(string $nombre, ?string $descripcion = null)
    {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }
}
