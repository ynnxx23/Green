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
    public ?string $imagen_url = null;
    public ?string $imagen_public_id = null;

    public function __construct(string $nombre, float $precio, ?string $categoria = null, ?string $imagen_url = null, ?string $imagen_public_id = null)
    {
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->categoria = $categoria;
        $this->imagen_url = $imagen_url;
        $this->imagen_public_id = $imagen_public_id;
    }
}
