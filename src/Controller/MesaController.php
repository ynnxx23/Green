<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Service\ServicioDeMesas;

class MesaController
{
    private ServicioDeMesas $servicioDeMesas;

    public function __construct(ServicioDeMesas $servicioDeMesas)
    {
        $this->servicioDeMesas = $servicioDeMesas;
    }

    public function manejar(string $ruta_interna, array $query_params): array
    {
        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            return $this->listarMesas($query_params);
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listarMesas(array $query_params): array
    {
        $id_area = isset($query_params['id_area']) ? (int) $query_params['id_area'] : 0;

        $mesas = $id_area > 0
            ? $this->servicioDeMesas->listarMesas($id_area)
            : $this->servicioDeMesas->listarTodasMesas();

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
