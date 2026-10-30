<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\BloqueMesa;

class BloqueMesaDao
{
    public function listarPorMesa(int $id_mesa): array
    {
        $sql = 'SELECT * FROM bloques_mesa WHERE id_mesa = :id_mesa ORDER BY fecha_inicio';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_mesa' => $id_mesa]);
        $filas = $stmt->fetchAll();

        $bloques = [];
        foreach ($filas as $fila) {
            $bloques[] = $this->mapearBloque($fila);
        }

        return $bloques;
    }

    public function obtenerPorId(int $id_bloque): ?BloqueMesa
    {
        $sql = 'SELECT * FROM bloques_mesa WHERE id_bloque = :id_bloque';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_bloque' => $id_bloque]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearBloque($fila);
    }

    public function crear(BloqueMesa $bloque, int $creado_por): int
    {
        $sql = 'INSERT INTO bloques_mesa (id_mesa, fecha_inicio, fecha_fin, motivo, creado_por, fecha_creacion)
                VALUES (:id_mesa, :fecha_inicio, :fecha_fin, :motivo, :creado_por, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':id_mesa' => $bloque->id_mesa,
            ':fecha_inicio' => $bloque->fecha_inicio,
            ':fecha_fin' => $bloque->fecha_fin,
            ':motivo' => $bloque->motivo,
            ':creado_por' => $creado_por,
        ]);

        return (int) Conexion::obtener()->lastInsertId();
    }

    public function desactivar(int $id_bloque): bool
    {
        $sql = 'DELETE FROM bloques_mesa WHERE id_bloque = :id_bloque';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_bloque' => $id_bloque]);

        return $stmt->rowCount() > 0;
    }

    public function existeSolapamiento(
        int $id_mesa,
        string $fecha_inicio,
        string $fecha_fin,
        ?int $id_bloque_excluir = null
    ): bool {
        $sql = 'SELECT COUNT(*) AS total
                FROM bloques_mesa
                WHERE id_mesa = :id_mesa
                  AND fecha_inicio < :fecha_fin
                  AND fecha_fin > :fecha_inicio';
        $params = [
            ':id_mesa' => $id_mesa,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin,
        ];

        if ($id_bloque_excluir !== null) {
            $sql .= ' AND id_bloque <> :id_bloque_excluir';
            $params[':id_bloque_excluir'] = $id_bloque_excluir;
        }

        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch();

        return (int) $fila['total'] > 0;
    }

    private function mapearBloque(array $fila): BloqueMesa
    {
        $bloque = new BloqueMesa(
            (int) $fila['id_mesa'],
            $fila['fecha_inicio'],
            $fila['fecha_fin'],
            $fila['motivo']
        );
        $bloque->id_bloque = (int) $fila['id_bloque'];
        $bloque->creado_por = $fila['creado_por'] ?? null;
        $bloque->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $bloque;
    }
}
