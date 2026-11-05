<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\ProductoDao;
use Restaurante\Model\Producto;
use Restaurante\Service\ServicioDeCloudinary;

class ServicioDeProductos
{
    private ProductoDao $productoDao;
    private ?ServicioDeCloudinary $cloudinary;

    public function __construct(ProductoDao $productoDao, ?ServicioDeCloudinary $cloudinary = null)
    {
        $this->productoDao = $productoDao;
        $this->cloudinary = $cloudinary;
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

    public function crearProducto(string $nombre, float $precio, ?string $categoria = null, ?string $imagen_url = null, ?string $imagen_public_id = null): int
    {
        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre es obligatorio.');
        }
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        $producto = new Producto($nombre, $precio, $categoria, $imagen_url, $imagen_public_id);
        return $this->productoDao->crear($producto);
    }

    public function editarProducto(int $id_producto, string $nombre, float $precio, ?string $categoria = null, ?string $imagen_url = null, ?string $imagen_public_id = null): bool
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

        // Si se sube una nueva imagen, se borra la anterior de Cloudinary si existía.
        if ($imagen_public_id !== null && $imagen_public_id !== '' && $this->cloudinary !== null) {
            if ($producto->imagen_public_id !== null && $producto->imagen_public_id !== '' && $producto->imagen_public_id !== $imagen_public_id) {
                $this->cloudinary->borrarImagen($producto->imagen_public_id);
            }
        }

        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->categoria = $categoria;
        $producto->imagen_url = $imagen_url;
        $producto->imagen_public_id = $imagen_public_id;

        return $this->productoDao->actualizar($producto);
    }

    public function obtenerProductoConImagen(int $id_producto): ?Producto
    {
        return $this->productoDao->obtenerPorId($id_producto);
    }

    public function desactivarProducto(int $id_producto): bool
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            throw new \InvalidArgumentException('El producto no existe.');
        }

        return $this->productoDao->desactivar($id_producto);
    }

    public function activarProducto(int $id_producto): bool
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            throw new \InvalidArgumentException('El producto no existe.');
        }

        $producto->activo = true;
        return $this->productoDao->actualizar($producto);
    }
}
