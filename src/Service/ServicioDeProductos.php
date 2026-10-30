<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\ProductoDao;
use Restaurante\Model\Producto;

class ServicioDeProductos
{
    private ProductoDao $productoDao;

    public function __construct(ProductoDao $productoDao)
    {
        $this->productoDao = $productoDao;
    }

    public function listarActivos(): array
    {
        return $this->productoDao->listarActivos();
    }

    public function listarTodos(): array
    {
        return $this->productoDao->listarTodos();
    }

    public function obtenerProducto(int $id_producto): ?Producto
    {
        return $this->productoDao->obtenerPorId($id_producto);
    }

    public function crearProducto(string $nombre, float $precio, ?string $categoria = null): int
    {
        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre es obligatorio.');
        }
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        $producto = new Producto($nombre, $precio, $categoria);
        return $this->productoDao->crear($producto);
    }

    public function editarProducto(int $id_producto, string $nombre, float $precio, ?string $categoria = null): bool
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            throw new \InvalidArgumentException('El producto no existe.');
        }
        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre es obligatorio.');
        }
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->categoria = $categoria;

        return $this->productoDao->actualizar($producto);
    }

    public function desactivarProducto(int $id_producto): bool
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            throw new \InvalidArgumentException('El producto no existe.');
        }

        return $this->productoDao->desactivar($id_producto);
    }
}
