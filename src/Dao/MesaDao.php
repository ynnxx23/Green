<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\Mesa;

class MesaDao
{
    public function listarPorArea(int $id_area, bool $soloActivas = true): array
    {
        $sql = 'SELECT * FROM mesas WHERE id_area = :id_area';
        $params = [':id_area' => $id_area];

        if ($soloActivas) {
            $sql .= ' AND activo = 1';
        }

        $sql .= ' ORDER BY numero_mesa';

        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        $mesas = [];
        foreach ($filas as $fila) {
            $mesa = new Mesa((int) $fila['id_area'], $fila['numero_mesa']);
            $mesa->id_mesa = (int) $fila['id_mesa'];
            $mesa->capacidad = (int) ($fila['capacidad'] ?? 1);
            $mesa->activo = (bool) $fila['activo'];
            $mesa->fecha_creacion = $fila['fecha_creacion'] ?? null;
            $mesas[] = $mesa;
        }

        return $mesas;
    }

    public function obtenerPorId(int $id_mesa): ?Mesa
    {
        $sql = 'SELECT * FROM mesas WHERE id_mesa = :id_mesa';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_mesa' => $id_mesa]);
        $fila = $stmt->fetch();

        if ($fila === false) {
            return null;
        }

        $mesa = new Mesa((int) $fila['id_area'], $fila['numero_mesa']);
        $mesa->id_mesa = (int) $fila['id_mesa'];
        $mesa->capacidad = (int) ($fila['capacidad'] ?? 1);
        $mesa->activo = (bool) $fila['activo'];
        $mesa->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $mesa;
    }
}
