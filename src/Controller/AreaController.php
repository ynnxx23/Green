<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Service\ServicioDeMesas;

class AreaController
{
    private ServicioDeMesas $servicioDeMesas;

    public function __construct(ServicioDeMesas $servicioDeMesas)
    {
        $this->servicioDeMesas = $servicioDeMesas;
    }

    public function listar(): array
    {
        $areas = $this->servicioDeMesas->listarAreas();

        $datos = [];
        foreach ($areas as $area) {
            $datos[] = [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'descripcion' => $area->descripcion,
                'activo' => $area->activo,
                'fecha_creacion' => $area->fecha_creacion,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Áreas obtenidas correctamente.',
        ];
    }
}
