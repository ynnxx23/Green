<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeCloudinary;
use Restaurante\Service\ServicioDeProductos;

class ProductoController
{
    private ServicioDeProductos $servicioDeProductos;
    private ServicioDeCloudinary $cloudinary;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDeProductos $servicioDeProductos, ServicioDeCloudinary $cloudinary, AuthMiddleware $authMiddleware)
    {
        $this->servicioDeProductos = $servicioDeProductos;
        $this->cloudinary = $cloudinary;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

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

            if ($metodo === 'PUT' && str_ends_with($ruta_interna, '/activar')) {
                return $this->activarProducto($id_producto);
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
        $entrada = $this->leerEntrada();

        $nombre = $entrada['nombre'] ?? '';
        $precio = (float) ($entrada['precio'] ?? 0);
        $categoria = $entrada['categoria'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        $imagen_url = $entrada['imagen_url'] ?? null;
        $imagen_public_id = $entrada['imagen_public_id'] ?? null;

        // Si viene archivo adjunto, subir a Cloudinary primero.
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $subida = $this->cloudinary->subirImagen($_FILES['imagen']['tmp_name'], $_FILES['imagen']['name']);

            if (!$subida['exito']) {
                return $this->error($subida['mensaje'] ?? 'Error al subir la imagen.', 400);
            }

            $imagen_url = $subida['url'];
            $imagen_public_id = $subida['public_id'];
        }

        try {
            $id_producto = $this->servicioDeProductos->crearProducto($nombre, $precio, $categoria, $imagen_url, $imagen_public_id);

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

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        $imagen_url = $entrada['imagen_url'] ?? null;
        $imagen_public_id = $entrada['imagen_public_id'] ?? null;

        // Si se quiere quitar la imagen, enviar imagen_public_id vacío o eliminar_imagen=1
        $quitarImagen = isset($entrada['eliminar_imagen']) && $entrada['eliminar_imagen'] === '1';

        // Si viene archivo adjunto, subir a Cloudinary primero.
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $subida = $this->cloudinary->subirImagen($_FILES['imagen']['tmp_name'], $_FILES['imagen']['name']);

            if (!$subida['exito']) {
                return $this->error($subida['mensaje'] ?? 'Error al subir la imagen.', 400);
            }

            $imagen_url = $subida['url'];
            $imagen_public_id = $subida['public_id'];
        } elseif ($quitarImagen) {
            $imagen_url = null;
            $imagen_public_id = null;
        }

        try {
            $resultado = $this->servicioDeProductos->editarProducto($id_producto, $nombre, $precio, $categoria, $imagen_url, $imagen_public_id);

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

    private function leerCuerpoJson(): array
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
    }

    /**
     * Lee datos del cuerpo. Soporta application/json y multipart/form-data.
     */
    private function leerEntrada(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_starts_with($contentType, 'multipart/form-data')) {
            $datos = array_merge($_POST, [
                'imagen_url' => $_POST['imagen_url'] ?? null,
                'imagen_public_id' => $_POST['imagen_public_id'] ?? null,
                'eliminar_imagen' => $_POST['eliminar_imagen'] ?? null,
            ]);

            return $datos;
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
                'imagen_url' => $producto->imagen_url,
                'imagen_public_id' => $producto->imagen_public_id,
            ];
        }

        return $resultado;
    }
}
