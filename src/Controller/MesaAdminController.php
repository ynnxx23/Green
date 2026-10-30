<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Dao\MesaDao;
use Restaurante\Middleware\AuthMiddleware;

class MesaAdminController
{
    private MesaDao $mesaDao;
    private AuthMiddleware $authMiddleware;

    public function __construct(MesaDao $mesaDao, AuthMiddleware $authMiddleware)
    {
        $this->mesaDao = $mesaDao;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        $this->authMiddleware->requiereAdministrador();

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->listar($query_params);
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

    private function listar(array $query_params): array
    {
        $id_area = isset($query_params['id_area']) ? (int) $query_params['id_area'] : 0;
        $soloActivas = !isset($query_params['incluir_inactivas']) || $query_params['incluir_inactivas'] !== '1';

        $mesas = $this->mesaDao->listarPorArea($id_area, $soloActivas);

        $datos = [];
        foreach ($mesas as $mesa) {
            $datos[] = [
                'id_mesa' => $mesa->id_mesa,
                'id_area' => $mesa->id_area,
                'numero_mesa' => $mesa->numero_mesa,
                'capacidad' => $mesa->capacidad,
                'activo' => $mesa->activo,
                'fecha_creacion' => $mesa->fecha_creacion,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Mesas obtenidas correctamente.',
        ];
    }

    private function crear(): array
    {
        $entrada = $this->leerCuerpoJson();

        $id_area = (int) ($entrada['id_area'] ?? 0);
        $numero_mesa = $entrada['numero_mesa'] ?? '';
        $capacidad = isset($entrada['capacidad']) ? (int) $entrada['capacidad'] : 1;

        if ($id_area <= 0 || $numero_mesa === '') {
            return $this->error('Faltan datos obligatorios: id_area, numero_mesa.', 400);
        }

        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $stmt = $pdo->prepare('
                INSERT INTO mesas
                    (id_area, numero_mesa, capacidad, activo, fecha_creacion)
                VALUES
                    (:id_area, :numero_mesa, :capacidad, 1, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([
                ':id_area' => $id_area,
                ':numero_mesa' => $numero_mesa,
                ':capacidad' => $capacidad,
            ]);
            $id = (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            return $this->error('Error al crear la mesa.', 500);
        }

        return [
            'exito' => true,
            'datos' => ['id_mesa' => $id],
            'mensaje' => 'Mesa creada correctamente.',
        ];
    }

    private function editar(int $id): array
    {
        $entrada = $this->leerCuerpoJson();

        $numero_mesa = $entrada['numero_mesa'] ?? null;
        $capacidad = isset($entrada['capacidad']) ? (int) $entrada['capacidad'] : null;
        $activo = isset($entrada['activo']) ? (bool) $entrada['activo'] : null;

        if ($numero_mesa === null && $capacidad === null && $activo === null) {
            return $this->error('No hay campos para actualizar.', 400);
        }

        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $campos = [];
            $params = [];

            if ($numero_mesa !== null) {
                $campos[] = 'numero_mesa = :numero_mesa';
                $params[':numero_mesa'] = $numero_mesa;
            }
            if ($capacidad !== null) {
                $campos[] = 'capacidad = :capacidad';
                $params[':capacidad'] = $capacidad;
            }
            if ($activo !== null) {
                $campos[] = 'activo = :activo';
                $params[':activo'] = $activo ? 1 : 0;
            }
            $campos[] = 'fecha_actualizacion = CURRENT_TIMESTAMP';
            $params[':id'] = $id;

            $sql = 'UPDATE mesas SET ' . implode(', ', $campos) . ' WHERE id_mesa = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return $this->error('La mesa no existe.', 404);
            }
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar la mesa.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Mesa actualizada correctamente.',
        ];
    }

    private function desactivar(int $id): array
    {
        try {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $stmt = $pdo->prepare('UPDATE mesas SET activo = 0, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_mesa = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return $this->error('La mesa no existe.', 404);
            }
        } catch (\PDOException $e) {
            return $this->error('Error al desactivar la mesa.', 500);
        }

        return [
            'exito' => true,
            'mensaje' => 'Mesa desactivada correctamente.',
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
