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

    // ========================================================================
    // LISTADO PARA CARTA PÚBLICA
    // ========================================================================
    public function listarParaCarta(?string $categoria = null, ?string $busqueda = null): array
    {
        return $this->productoDao->listarActivosConDetalles($categoria, $busqueda);
    }

    public function listarCategorias(): array
    {
        return $this->productoDao->listarCategorias();
    }

    public function listarActivos(): array
    {
        return $this->productoDao->listarActivos();
    }

    public function listarTodos(): array
    {
        return $this->productoDao->listarTodos();
    }

    // ========================================================================
    // DETALLE DE PRODUCTO (para ver en la carta)
    // ========================================================================
    public function obtenerProducto(int $id_producto): ?array
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            return null;
        }

        $stock = $this->productoDao->obtenerStock($id_producto);

        return [
            'id_producto' => $producto->id_producto,
            'nombre' => $producto->nombre,
            'categoria' => $producto->categoria,
            'precio' => (float) $producto->precio,
            'activo' => (bool) $producto->activo,
            'imagen_url' => $producto->imagen_url,
            'imagen_public_id' => $producto->imagen_public_id,
            'fecha_creacion' => $producto->fecha_creacion,
            'stock_actual' => $stock ? (int) $stock['stock_actual'] : 0,
            'disponible' => $stock ? (bool) ($stock['stock_actual'] > 0) : false,
        ];
    }

    // ========================================================================
    // CRUD (administrador/encargado)
    // ========================================================================
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

        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->categoria = $categoria;
        $producto->imagen_url = $imagen_url;
        $producto->imagen_public_id = $imagen_public_id;

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

    public function activarProducto(int $id_producto): bool
    {
        $producto = $this->productoDao->obtenerPorId($id_producto);
        if ($producto === null) {
            throw new \InvalidArgumentException('El producto no existe.');
        }

        $producto->activo = true;
        return $this->productoDao->actualizar($producto);
    }

    // ========================================================================
    // STOCK
    // ========================================================================
    public function actualizarStock(int $id_producto, int $stock_actual, int $limite_venta): bool
    {
        if ($stock_actual < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo.');
        }
        if ($limite_venta < 0) {
            throw new \InvalidArgumentException('El límite de venta no puede ser negativo.');
        }

        return $this->productoDao->actualizarStock($id_producto, $stock_actual, $limite_venta);
    }

    public function obtenerStock(int $id_producto): ?array
    {
        return $this->productoDao->obtenerStock($id_producto);
    }

    public function listarStockBajo(): array
    {
        return $this->productoDao->listarStockBajo();
    }
}
