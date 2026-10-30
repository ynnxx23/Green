<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeProductos;

class ProductoController
{
    private ServicioDeProductos $servicioDeProductos;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDeProductos $servicioDeProductos, AuthMiddleware $authMiddleware)
    {
        $this->servicioDeProductos = $servicioDeProductos;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo): array
    {
        $this->authMiddleware->requiereAdministrador();

        if ($ruta_interna === 'activos') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->listarActivos();
        }

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo === 'GET') {
                return $this->listarTodos();
            }
            if ($metodo === 'POST') {
                return $this->crearProducto();
            }
            return $this->error('Método no permitido.', 405);
        }

        if (preg_match('/^(\d+)$/', $ruta_interna, $coincidencias)) {
            $id_producto = (int) $coincidencias[1];

            if ($metodo === 'PUT') {
                return $this->editarProducto($id_producto);
            }

            if ($metodo === 'DELETE') {
                return $this->desactivarProducto($id_producto);
            }
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listarActivos(): array
    {
        $productos = $this->servicioDeProductos->listarActivos();

        return [
            'exito' => true,
            'datos' => $this->mapearProductos($productos),
            'mensaje' => 'Productos activos obtenidos correctamente.',
        ];
    }

    private function listarTodos(): array
    {
        $productos = $this->servicioDeProductos->listarTodos();

        return [
            'exito' => true,
            'datos' => $this->mapearProductos($productos),
            'mensaje' => 'Todos los productos obtenidos correctamente.',
        ];
    }

    private function crearProducto(): array
    {
        $entrada = $this->leerCuerpoJson();

        $nombre = $entrada['nombre'] ?? '';
        $precio = (float) ($entrada['precio'] ?? 0);
        $categoria = $entrada['categoria'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        try {
            $id_producto = $this->servicioDeProductos->crearProducto($nombre, $precio, $categoria);

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
        $entrada = $this->leerCuerpoJson();

        $nombre = $entrada['nombre'] ?? '';
        $precio = (float) ($entrada['precio'] ?? 0);
        $categoria = $entrada['categoria'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        try {
            $resultado = $this->servicioDeProductos->editarProducto($id_producto, $nombre, $precio, $categoria);

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

    private function leerCuerpoJson(): array
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
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

    private function mapearProductos(array $productos): array
    {
        $resultado = [];
        foreach ($productos as $producto) {
            $resultado[] = [
                'id_producto' => $producto->id_producto,
                'nombre' => $producto->nombre,
                'categoria' => $producto->categoria,
                'precio' => $producto->precio,
                'activo' => $producto->activo,
                'fecha_creacion' => $producto->fecha_creacion,
            ];
        }

        return $resultado;
    }
}
