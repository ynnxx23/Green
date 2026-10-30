<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeHorarios;

class HorarioAdminController
{
    private ServicioDeHorarios $servicioDeHorarios;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDeHorarios $servicioDeHorarios, AuthMiddleware $authMiddleware)
    {
        $this->servicioDeHorarios = $servicioDeHorarios;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

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
        $id_area = isset($query_params['area_id']) ? (int) $query_params['area_id'] : 0;

        if ($id_area <= 0) {
            return $this->error('Se requiere el parámetro area_id.', 400);
        }

        $horarios = $this->servicioDeHorarios->listarPorArea($id_area);

        $datos = [];
        foreach ($horarios as $horario) {
            $datos[] = [
                'id_horario' => $horario->id_horario,
                'id_area' => $horario->id_area,
                'dia_semana' => $horario->dia_semana,
                'hora_apertura' => $horario->hora_apertura,
                'hora_cierre' => $horario->hora_cierre,
                'activo' => $horario->activo,
                'fecha_creacion' => $horario->fecha_creacion,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Horarios obtenidos correctamente.',
        ];
    }

    private function crear(): array
    {
        $entrada = $this->leerCuerpoJson();

        $id_area = (int) ($entrada['area_id'] ?? 0);
        $dia_semana = (int) ($entrada['dia_semana'] ?? -1);
        $hora_apertura = $entrada['hora_apertura'] ?? '';
        $hora_cierre = $entrada['hora_cierre'] ?? '';

        if ($id_area <= 0 || $hora_apertura === '' || $hora_cierre === '') {
            return $this->error('Faltan datos obligatorios: area_id, hora_apertura, hora_cierre.', 400);
        }

        try {
            $id = $this->servicioDeHorarios->crearHorario($id_area, $dia_semana, $hora_apertura, $hora_cierre);

            return [
                'exito' => true,
                'datos' => ['id_horario' => $id],
                'mensaje' => 'Horario creado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al crear el horario.', 500);
        }
    }

    private function editar(int $id_horario): array
    {
        $entrada = $this->leerCuerpoJson();

        $hora_apertura = $entrada['hora_apertura'] ?? null;
        $hora_cierre = $entrada['hora_cierre'] ?? null;

        if ($hora_apertura === null || $hora_cierre === null) {
            return $this->error('Faltan datos obligatorios: hora_apertura, hora_cierre.', 400);
        }

        try {
            $resultado = $this->servicioDeHorarios->editarHorario($id_horario, $hora_apertura, $hora_cierre);

            if (!$resultado) {
                return $this->error('El horario no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Horario editado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al editar el horario.', 500);
        }
    }

    private function desactivar(int $id_horario): array
    {
        try {
            $resultado = $this->servicioDeHorarios->desactivarHorario($id_horario);

            if (!$resultado) {
                return $this->error('El horario no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Horario desactivado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al desactivar el horario.', 500);
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
}
