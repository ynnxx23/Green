<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Service\ServicioDeProductos;

class ProductoController
{
    private ServicioDeProductos $servicioDeProductos;

    public function __construct(ServicioDeProductos $servicioDeProductos)
    {
        $this->servicioDeProductos = $servicioDeProductos;
    }

    public function manejar(string $ruta_interna, string $metodo): array
    {
        // GET público: listar productos para la carta
        if ($metodo === 'GET') {
            if ($ruta_interna === '' || $ruta_interna === 'listar' || $ruta_interna === 'activos') {
                return $this->listarParaCarta();
            }

            if ($ruta_interna === 'categorias') {
                return $this->listarCategorias();
            }

            if (preg_match('/^(\d+)$/', $ruta_interna, $c)) {
                return $this->obtenerProducto((int) $c[1]);
            }

            if ($ruta_interna === 'buscar') {
                return $this->buscar();
            }
        }

        // El resto requiere autenticación (encargado/administrador)
        // Nota: el middleware se aplica en el router (index.php) para estas rutas
        if ($metodo === 'POST' && ($ruta_interna === '' || $ruta_interna === 'listar')) {
            return $this->crearProducto();
        }

        if (preg_match('/^(\d+)$/', $ruta_interna, $c)) {
            $id_producto = (int) $c[1];

            if ($metodo === 'PUT') {
                return $this->editarProducto($id_producto);
            }

            if ($metodo === 'DELETE') {
                return $this->desactivarProducto($id_producto);
            }

            if ($metodo === 'PUT' && str_ends_with($ruta_interna, '/activar')) {
                return $this->activarProducto($id_producto);
            }

            if ($metodo === 'PUT' && str_ends_with($ruta_interna, '/stock')) {
                return $this->actualizarStock($id_producto);
            }
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    // ========================================================================
    // CARTA PÚBLICA
    // ========================================================================
    private function listarParaCarta(): array
    {
        $categoria = $_GET['categoria'] ?? null;
        $busqueda = $_GET['q'] ?? null;

        $productos = $this->servicioDeProductos->listarParaCarta($categoria, $busqueda);

        return [
            'exito' => true,
            'datos' => $productos,
            'total' => count($productos),
            'mensaje' => 'Productos de la carta obtenidos correctamente.',
        ];
    }

    private function listarCategorias(): array
    {
        $categorias = $this->servicioDeProductos->listarCategorias();

        return [
            'exito' => true,
            'datos' => $categorias,
            'mensaje' => 'Categorías obtenidas correctamente.',
        ];
    }

    private function obtenerProducto(int $id_producto): array
    {
        $producto = $this->servicioDeProductos->obtenerProducto($id_producto);

        if ($producto === null) {
            return $this->error('Producto no encontrado.', 404);
        }

        return [
            'exito' => true,
            'datos' => $producto,
            'mensaje' => 'Producto obtenido correctamente.',
        ];
    }

    private function buscar(): array
    {
        $q = $_GET['q'] ?? '';

        if ($q === '') {
            return $this->error('Se requiere el parámetro de búsqueda q.', 400);
        }

        $productos = $this->servicioDeProductos->listarParaCarta(null, $q);

        return [
            'exito' => true,
            'datos' => $productos,
            'total' => count($productos),
            'mensaje' => 'Resultados de búsqueda obtenidos.',
        ];
    }

    // ========================================================================
    // CRUD ADMIN
    // ========================================================================
    private function crearProducto(): array
    {
        $entrada = $this->leerEntrada();

        $nombre = $entrada['nombre'] ?? '';
        $precio = (float) ($entrada['precio'] ?? 0);
        $categoria = $entrada['categoria'] ?? null;
        $imagen_url = $entrada['imagen_url'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        try {
            $id_producto = $this->servicioDeProductos->crearProducto($nombre, $precio, $categoria, $imagen_url);

            return [
                'exito' => true,
                'datos' => ['id_producto' => $id_producto],
                'mensaje' => 'Producto creado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al crear el producto.', 500);
        }
    }

    private function editarProducto(int $id_producto): array
    {
        $entrada = $this->leerEntrada();

        $nombre = $entrada['nombre'] ?? '';
        $precio = (float) ($entrada['precio'] ?? 0);
        $categoria = $entrada['categoria'] ?? null;
        $imagen_url = $entrada['imagen_url'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        try {
            $resultado = $this->servicioDeProductos->editarProducto($id_producto, $nombre, $precio, $categoria, $imagen_url);

            if (!$resultado) {
                return $this->error('El producto no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Producto editado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al editar el producto.', 500);
        }
    }

    private function desactivarProducto(int $id_producto): array
    {
        try {
            $resultado = $this->servicioDeProductos->desactivarProducto($id_producto);

            if (!$resultado) {
                return $this->error('El producto no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Producto desactivado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al desactivar el producto.', 500);
        }
    }

    private function activarProducto(int $id_producto): array
    {
        try {
            $resultado = $this->servicioDeProductos->activarProducto($id_producto);

            if (!$resultado) {
                return $this->error('El producto no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Producto activado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al activar el producto.', 500);
        }
    }

    private function actualizarStock(int $id_producto): array
    {
        $entrada = $this->leerCuerpoJson();

        $stock_actual = isset($entrada['stock_actual']) ? (int) $entrada['stock_actual'] : 0;
        $limite_venta = isset($entrada['limite_venta']) ? (int) $entrada['limite_venta'] : 0;

        try {
            $resultado = $this->servicioDeProductos->actualizarStock($id_producto, $stock_actual, $limite_venta);

            if (!$resultado) {
                return $this->error('El producto no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Stock actualizado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar el stock.', 500);
        }
    }

    // ========================================================================
    // HELPERS
    // ========================================================================
    private function leerCuerpoJson(): array
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
    }

    private function leerEntrada(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_starts_with($contentType, 'multipart/form-data')) {
            return array_merge($_POST, [
                'imagen_url' => $_POST['imagen_url'] ?? null,
            ]);
        }

        return $this->leerCuerpoJson();
    }

    private function error(string $mensaje, int $codigo_http): array
    {
        http_response_code($codigo_http);

        return [
            'exito' => false,
            'datos' => null,
            'mensaje' => $mensaje,
            'codigo' => $codigo_http,
        ];
    }
}
