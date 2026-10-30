<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\Area;

class AreaDao
{
    public function listarActivas(): array
    {
        $sql = 'SELECT * FROM areas WHERE activo = 1 ORDER BY nombre';
        $stmt = Conexion::obtener()->query($sql);
        $filas = $stmt->fetchAll();

        $areas = [];
        foreach ($filas as $fila) {
            $area = new Area($fila['nombre'], $fila['descripcion']);
            $area->id_area = (int) $fila['id_area'];
            $area->activo = (bool) $fila['activo'];
            $area->fecha_creacion = $fila['fecha_creacion'] ?? null;
            $areas[] = $area;
        }

        return $areas;
    }

    public function obtenerPorId(int $id_area): ?Area
    {
        $sql = 'SELECT * FROM areas WHERE id_area = :id_area';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_area' => $id_area]);
        $fila = $stmt->fetch();

        if ($fila === false) {
            return null;
        }

        $area = new Area($fila['nombre'], $fila['descripcion']);
        $area->id_area = (int) $fila['id_area'];
        $area->activo = (bool) $fila['activo'];
        $area->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $area;
    }
}
