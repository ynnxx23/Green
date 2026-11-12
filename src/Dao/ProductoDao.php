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

    // ========================================================================
    // CARTA PÚBLICA
    // ========================================================================
    public function listarActivosConDetalles(?string $categoria = null, ?string $busqueda = null): array
    {
        $sql = '
            SELECT p.*, 
                   COALESCE(AVG(c.puntuacion), 0) AS calificacion_promedio,
                   COUNT(c.id_calificacion) AS total_calificaciones,
                   sp.stock_actual,
                   sp.limite_venta
            FROM productos p
            LEFT JOIN calificaciones_platos c ON c.id_producto = p.id_producto AND c.activo = 1
            LEFT JOIN stock_producto sp ON sp.id_producto = p.id_producto AND sp.activo = 1
            WHERE p.activo = 1
        ';
        $params = [];

        if ($categoria !== null && $categoria !== '') {
            $sql .= ' AND p.categoria = :categoria';
            $params[':categoria'] = $categoria;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= ' AND (p.nombre LIKE :busqueda OR p.categoria LIKE :busqueda)';
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= ' GROUP BY p.id_producto, p.nombre, p.categoria, p.precio, p.activo, p.imagen_url, p.imagen_public_id, p.fecha_creacion, sp.stock_actual, sp.limite_venta';
        $sql .= ' ORDER BY p.categoria, p.nombre';

        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        $productos = [];
        foreach ($filas as $fila) {
            $productos[] = [
                'id_producto' => (int) $fila['id_producto'],
                'nombre' => $fila['nombre'],
                'categoria' => $fila['categoria'],
                'precio' => (float) $fila['precio'],
                'activo' => (bool) $fila['activo'],
                'imagen_url' => $fila['imagen_url'] ?? null,
                'imagen_public_id' => $fila['imagen_public_id'] ?? null,
                'fecha_creacion' => $fila['fecha_creacion'] ?? null,
                'calificacion_promedio' => round((float) $fila['calificacion_promedio'], 2),
                'total_calificaciones' => (int) $fila['total_calificaciones'],
                'stock_actual' => $fila['stock_actual'] !== null ? (int) $fila['stock_actual'] : 0,
                'disponible' => $fila['stock_actual'] !== null ? (bool) ((int) $fila['stock_actual'] > 0) : true,
            ];
        }

        return $productos;
    }

    public function listarCategorias(): array
    {
        $sql = 'SELECT DISTINCT categoria FROM productos WHERE activo = 1 AND categoria IS NOT NULL AND categoria != "" ORDER BY categoria';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $categorias = [];
        foreach ($filas as $fila) {
            $categorias[] = $fila['categoria'];
        }

        return $categorias;
    }

    public function obtenerPorId(int $id_producto): ?Producto
    {
        $sql = 'SELECT * FROM productos WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearProducto($fila);
    }

    public function obtenerCalificacionPromedio(int $id_producto): float
    {
        $sql = 'SELECT COALESCE(AVG(puntuacion), 0) AS promedio FROM calificaciones_platos WHERE id_producto = :id_producto AND activo = 1';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila !== false ? (float) $fila['promedio'] : 0.0;
    }

    public function contarCalificaciones(int $id_producto): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM calificaciones_platos WHERE id_producto = :id_producto AND activo = 1';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila !== false ? (int) $fila['total'] : 0;
    }

    // ========================================================================
    // STOCK
    // ========================================================================
    public function obtenerStock(int $id_producto): ?array
    {
        $sql = 'SELECT stock_actual, limite_venta FROM stock_producto WHERE id_producto = :id_producto AND activo = 1';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function actualizarStock(int $id_producto, int $stock_actual, int $limite_venta): bool
    {
        $existe = Conexion::obtener()->prepare('SELECT COUNT(*) FROM stock_producto WHERE id_producto = :id_producto');
        $existe->execute([':id_producto' => $id_producto]);

        if ((int) $existe->fetchColumn() > 0) {
            $sql = 'UPDATE stock_producto SET stock_actual = :stock_actual, limite_venta = :limite_venta, fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_producto = :id_producto';
            $stmt = Conexion::obtener()->prepare($sql);
            $stmt->execute([
                ':stock_actual' => $stock_actual,
                ':limite_venta' => $limite_venta,
                ':id_producto' => $id_producto,
            ]);
        } else {
            $sql = 'INSERT INTO stock_producto (id_producto, stock_actual, limite_venta, activo, fecha_actualizacion)
                    VALUES (:id_producto, :stock_actual, :limite_venta, 1, CURRENT_TIMESTAMP)';
            $stmt = Conexion::obtener()->prepare($sql);
            $stmt->execute([
                ':id_producto' => $id_producto,
                ':stock_actual' => $stock_actual,
                ':limite_venta' => $limite_venta,
            ]);
        }

        return true;
    }

    public function listarStockBajo(): array
    {
        $sql = '
            SELECT p.id_producto, p.nombre, p.categoria, p.precio, sp.stock_actual, sp.limite_venta
            FROM productos p
            JOIN stock_producto sp ON sp.id_producto = p.id_producto AND sp.activo = 1
            WHERE p.activo = 1
              AND (sp.stock_actual <= sp.limite_venta OR sp.stock_actual <= 5)
            ORDER BY sp.stock_actual ASC
        ';
        $stmt = Conexion::obtener()->query($sql);

        return $stmt->fetchAll();
    }

    public function crear(Producto $producto): int
    {
        $sql = 'INSERT INTO productos (nombre, categoria, precio, activo, imagen_url, imagen_public_id, fecha_creacion)
                VALUES (:nombre, :categoria, :precio, :activo, :imagen_url, :imagen_public_id, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':categoria' => $producto->categoria,
            ':precio' => $producto->precio,
            ':activo' => $producto->activo ? 1 : 0,
            ':imagen_url' => $producto->imagen_url,
            ':imagen_public_id' => $producto->imagen_public_id,
        ]);

        return (int) Conexion::obtener()->lastInsertId();
    }

    public function actualizar(Producto $producto): bool
    {
        $sql = 'UPDATE productos
                    SET nombre = :nombre, categoria = :categoria, precio = :precio, activo = :activo,
                        imagen_url = :imagen_url, imagen_public_id = :imagen_public_id,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':categoria' => $producto->categoria,
            ':precio' => $producto->precio,
            ':activo' => $producto->activo ? 1 : 0,
            ':imagen_url' => $producto->imagen_url,
            ':imagen_public_id' => $producto->imagen_public_id,
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
        $producto = new Producto(
            $this->fixEncoding($fila['nombre']),
            (float) $fila['precio'],
            $this->fixEncoding($fila['categoria']),
            $fila['imagen_url'] ?? null,
            $fila['imagen_public_id'] ?? null
        );
        $producto->id_producto = (int) $fila['id_producto'];
        $producto->activo = (bool) $fila['activo'];
        $producto->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $producto;
    }

    private function fixEncoding(string $texto): string
    {
        // Corregir doble codificación UTF-8 (ej: "CafÃ© expreso" -> "Café expreso")
        if (mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1'], true) === 'ISO-8859-1') {
            return mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }
        return $texto;
    }
}
