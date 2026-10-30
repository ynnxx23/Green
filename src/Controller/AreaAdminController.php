<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Dao\AreaDao;
use Restaurante\Middleware\AuthMiddleware;

class AreaAdminController
{
    private AreaDao $areaDao;
    private AuthMiddleware $authMiddleware;

    public function __construct(AreaDao $areaDao, AuthMiddleware $authMiddleware)
    {
        $this->areaDao = $areaDao;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo): array
    {
        $this->authMiddleware->requiereAdministrador();

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->listar();
        }

        if ($metodo === 'POST' && $ruta_interna === '') {
            return $this->crear();
        }

        if (preg_match('/^(\d+)$/', $ruta_interna, $coincidencias)) {
            $id = (int) $coincidencias[1];

            if ($metodo === 'PUT') {
                return $this->editar($id);
            }

            if ($metodo === 'DELETE') {
                return $this->desactivar($id);
            }
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listar(): array
    {
        $sql = 'SELECT * FROM areas ORDER BY nombre';
        $stmt = \Restaurante\Database\Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $datos = [];
        foreach ($filas as $fila) {
            $datos[] = [
                'id_area' => (int) $fila['id_area'],
                'nombre' => $fila['nombre'],
                'descripcion' => $fila['descripcion'],
                'activo' => (bool) $fila['activo'],
                'fecha_creacion' => $fila['fecha_creacion'],
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Áreas obtenidas correctamente.',
        ];
    }

    private function crear(): array
    {
        $entrada = $this->leerCuerpoJson();
        $nombre = $entrada['nombre'] ?? '';
        $descripcion = $entrada['descripcion'] ?? null;

        if ($nombre === '') {
            return $this->error('El nombre es obligatorio.', 400);
        }

        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $stmt = $pdo->prepare('
                INSERT INTO areas (nombre, descripcion, activo, fecha_creacion)
                VALUES (:nombre, :descripcion, 1, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
            ]);
            $id = (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            return $this->error('Error al crear el área.', 500);
        }

        return [
            'exito' => true,
            'datos' => ['id_area' => $id],
            'mensaje' => 'Área creada correctamente.',
        ];
    }

    private function editar(int $id): array
    {
        $entrada = $this->leerCuerpoJson();
        $nombre = $entrada['nombre'] ?? null;
        $descripcion = $entrada['descripcion'] ?? null;
        $activo = isset($entrada['activo']) ? (bool) $entrada['activo'] : null;

        if ($nombre === null && $descripcion === null && $activo === null) {
            return $this->error('No hay campos para actualizar.', 400);
        }

        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $campos = [];
            $params = [];

            if ($nombre !== null) {
                $campos[] = 'nombre = :nombre';
                $params[':nombre'] = $nombre;
            }
            if ($descripcion !== null) {
                $campos[] = 'descripcion = :descripcion';
                $params[':descripcion'] = $descripcion;
            }
            if ($activo !== null) {
                $campos[] = 'activo = :activo';
                $params[':activo'] = $activo ? 1 : 0;
            }
            $campos[] = 'fecha_actualizacion = CURRENT_TIMESTAMP';
            $params[':id'] = $id;

            $sql = 'UPDATE areas SET ' . implode(', ', $campos) . ' WHERE id_area = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return $this->error('El área no existe.', 404);
            }
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar el área.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Área actualizada correctamente.',
        ];
    }

    private function desactivar(int $id): array
    {
        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $stmt = $pdo->prepare('UPDATE areas SET activo = 0, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_area = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->error('El área no existe.', 404);
            }
        } catch (\PDOException $e) {
            return $this->error('Error al desactivar el área.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Área desactivada correctamente.',
        ];
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
}
