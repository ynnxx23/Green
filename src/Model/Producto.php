<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Producto
{
    public ?int $id_producto = null;
    public string $nombre;
    public ?string $categoria = null;
    public float $precio = 0.0;
    public bool $activo = true;
    public ?string $fecha_creacion = null;

    public function __construct(string $nombre, float $precio, ?string $categoria = null)
    {
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->categoria = $categoria;
    }
}
