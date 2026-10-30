<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Rol
{
    public ?int $id_rol = null;
    public string $nombre;
    public ?string $descripcion = null;
    public ?string $fecha_creacion = null;

    public function __construct(string $nombre, ?string $descripcion = null)
    {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }
}
