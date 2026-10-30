<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Service\ServicioDeMesas;
use Restaurante\Service\ServicioDeDisponibilidad;

class MesaController
{
    private ServicioDeMesas $servicioDeMesas;
    private ServicioDeDisponibilidad $servicioDeDisponibilidad;

    public function __construct(
        ServicioDeMesas $servicioDeMesas,
        ServicioDeDisponibilidad $servicioDeDisponibilidad
    ) {
        $this->servicioDeMesas = $servicioDeMesas;
        $this->servicioDeDisponibilidad = $servicioDeDisponibilidad;
    }

    public function manejar(string $ruta_interna, array $query_params): array
    {
        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            return $this->listarMesas($query_params);
        }

        if ($ruta_interna === 'disponibilidad') {
            return $this->disponibilidad($query_params);
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listarMesas(array $query_params): array
    {
        $id_area = isset($query_params['id_area']) ? (int) $query_params['id_area'] : 0;

        if ($id_area <= 0) {
            return $this->error('Se requiere el parámetro id_area.', 400);
        }

        $mesas = $this->servicioDeMesas->listarMesas($id_area);

        $datos = [];
        foreach ($mesas as $mesa) {
            $datos[] = [
                'id_mesa' => $mesa->id_mesa,
                'id_area' => $mesa->id_area,
                'numero_mesa' => $mesa->numero_mesa,
                'capacidad' => $mesa->capacidad,
                'activo' => $mesa->activo,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Mesas obtenidas correctamente.',
        ];
    }

    private function disponibilidad(array $query_params): array
    {
        $id_mesa = isset($query_params['id_mesa']) ? (int) $query_params['id_mesa'] : 0;
        $fecha = $query_params['fecha'] ?? '';

        if ($id_mesa <= 0) {
            return $this->error('Se requiere el parámetro id_mesa.', 400);
        }

        if ($fecha === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $this->error('Se requiere el parámetro fecha con formato YYYY-MM-DD.', 400);
        }

        $disponibles = $this->servicioDeDisponibilidad->calcularDisponibilidad($id_mesa, $fecha);

        return [
            'exito' => true,
            'datos' => [
                'id_mesa' => $id_mesa,
                'fecha' => $fecha,
                'horarios_disponibles' => $disponibles,
            ],
            'mensaje' => 'Disponibilidad obtenida correctamente.',
        ];
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
