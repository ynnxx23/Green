<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\Producto;

class ProductoDao
{
    public function listarActivos(): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria FROM productos p
                LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE p.activo = 1 ORDER BY c.nombre, p.nombre';
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
        $sql = 'SELECT p.*, c.nombre AS categoria FROM productos p
                LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                ORDER BY p.nombre';
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
                   c.nombre AS categoria,
                   sp.stock_actual,
                   sp.limite_venta
            FROM productos p
            LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
            LEFT JOIN stock_producto sp ON sp.id_producto = p.id_producto AND sp.activo = 1
            WHERE p.activo = 1
        ';
        $params = [];

        if ($categoria !== null && $categoria !== '') {
            $sql .= ' AND c.nombre = :categoria';
            $params[':categoria'] = $categoria;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= ' AND (p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)';
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= ' ORDER BY c.nombre, p.nombre';

        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        $productos = [];
        foreach ($filas as $fila) {
            $productos[] = [
                'id_producto' => (int) $fila['id_producto'],
                'nombre' => $fila['nombre'],
                'categoria' => $fila['categoria'] ?? 'Sin categoría',
                'precio' => (float) $fila['precio'],
                'activo' => (bool) $fila['activo'],
                'imagen_url' => $fila['imagen_url'] ?? null,
                'imagen_public_id' => $fila['imagen_public_id'] ?? null,
                'fecha_creacion' => $fila['fecha_creacion'] ?? null,
                'stock_actual' => $fila['stock_actual'] !== null ? (int) $fila['stock_actual'] : 0,
                'disponible' => $fila['stock_actual'] !== null ? (bool) ((int) $fila['stock_actual'] > 0) : true,
            ];
        }

        return $productos;
    }

    public function listarCategorias(): array
    {
        $sql = 'SELECT c.nombre FROM categorias c
                JOIN productos p ON p.id_categoria = c.id_categoria AND p.activo = 1
                WHERE c.activo = 1
                GROUP BY c.id_categoria, c.nombre
                ORDER BY c.nombre';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $categorias = [];
        foreach ($filas as $fila) {
            $categorias[] = $fila['nombre'];
        }

        return $categorias;
    }

    public function obtenerPorId(int $id_producto): ?Producto
    {
        $sql = 'SELECT p.*, c.nombre AS categoria FROM productos p
                LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE p.id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_producto' => $id_producto]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearProducto($fila);
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
            SELECT p.id_producto, p.nombre, c.nombre AS categoria, p.precio,
                   sp.stock_actual, sp.limite_venta
            FROM productos p
            LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
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
        $sql = 'INSERT INTO productos (nombre, id_categoria, precio, activo, imagen_url, imagen_public_id, fecha_creacion)
                VALUES (:nombre, :id_categoria, :precio, :activo, :imagen_url, :imagen_public_id, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':id_categoria' => $this->resolverCategoria($producto->categoria),
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
                    SET nombre = :nombre, id_categoria = :id_categoria, precio = :precio, activo = :activo,
                        imagen_url = :imagen_url, imagen_public_id = :imagen_public_id,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_producto = :id_producto';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':nombre' => $producto->nombre,
            ':id_categoria' => $this->resolverCategoria($producto->categoria),
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

    /**
     * Resuelve el id de categoria a partir de su nombre, creandola si
     * todavia no existe. Mantiene la coherencia con el catalogo de la carta.
     */
    private function resolverCategoria(?string $nombre): ?int
    {
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            return null;
        }

        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare('SELECT id_categoria FROM categorias WHERE nombre = :n');
        $stmt->execute([':n' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        $ins = $pdo->prepare('INSERT INTO categorias (nombre) VALUES (:n)');
        $ins->execute([':n' => $nombre]);

        return (int) $pdo->lastInsertId();
    }

    private function mapearProducto(array $fila): Producto
    {
        $producto = new Producto(
            $this->fixEncoding($fila['nombre']),
            (float) $fila['precio'],
            $this->fixEncoding($fila['categoria'] ?? 'Sin categoría'),
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
        // Corregir doble codificacion UTF-8 (ej: "CafA9 expreso" -> "Cafe expreso")
        if (mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1'], true) === 'ISO-8859-1') {
            return mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }
        return $texto;
    }
}
