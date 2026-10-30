<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\Producto;

class ProductoDao
{
    public function listarActivos(): array
    {
        $sql = 'SELECT * FROM productos WHERE activo = 1 ORDER BY categoria, nombre';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $productos = [];
        foreach ($filas as $fila) {
            $productos[] = $this->mapearProducto($fila);
        }

        return $productos;
    }

    public function listarTodos(): array
    {
        $sql = 'SELECT * FROM productos ORDER BY nombre';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $productos = [];
        foreach ($filas as $fila) {
            $productos[] = $this->mapearProducto($fila);
        }

        return $productos;
    }

    public function obtenerPorId(int $id_producto): ?Producto
    {
        $sql = 'SELECT * FROM productos WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearProducto($fila);
    }

    public function crear(Producto $producto): int
    {
        $sql = 'INSERT INTO productos (nombre, categoria, precio, activo, fecha_creacion)
                VALUES (:nombre, :categoria, :precio, :activo, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':categoria' => $producto->categoria,
            ':precio' => $producto->precio,
            ':activo' => $producto->activo ? 1 : 0,
        ]);

        return (int) Conexion::obtener()->lastInsertId();
    }

    public function actualizar(Producto $producto): bool
    {
        $sql = 'UPDATE productos
                    SET nombre = :nombre, categoria = :categoria, precio = :precio, activo = :activo,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':categoria' => $producto->categoria,
            ':precio' => $producto->precio,
            ':activo' => $producto->activo ? 1 : 0,
            ':id_producto' => $producto->id_producto,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function desactivar(int $id_producto): bool
    {
        $sql = 'UPDATE productos SET activo = 0, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);

        return $stmt->rowCount() > 0;
    }

    private function mapearProducto(array $fila): Producto
    {
        $producto = new Producto($fila['nombre'], (float) $fila['precio'], $fila['categoria']);
        $producto->id_producto = (int) $fila['id_producto'];
        $producto->activo = (bool) $fila['activo'];
        $producto->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $producto;
    }
}
