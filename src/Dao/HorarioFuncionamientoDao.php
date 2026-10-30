<?php

declare(strict_types=1);

namespace Restaurante\Dao;

use Restaurante\Database\Conexion;
use Restaurante\Model\HorarioFuncionamiento;

class HorarioFuncionamientoDao
{
    public function listarPorArea(int $id_area): array
    {
        $sql = 'SELECT * FROM horarios_funcionamiento WHERE id_area = :id_area ORDER BY dia_semana';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_area' => $id_area]);
        $filas = $stmt->fetchAll();

        $horarios = [];
        foreach ($filas as $fila) {
            $horario = new HorarioFuncionamiento(
                (int) $fila['id_area'],
                (int) $fila['dia_semana'],
                $fila['hora_apertura'],
                $fila['hora_cierre']
            );
            $horario->id_horario = (int) $fila['id_horario'];
            $horario->activo = (bool) $fila['activo'];
            $horario->fecha_creacion = $fila['fecha_creacion'] ?? null;
            $horarios[] = $horario;
        }

        return $horarios;
    }

    public function obtenerPorId(int $id_horario): ?HorarioFuncionamiento
    {
        $sql = 'SELECT * FROM horarios_funcionamiento WHERE id_horario = :id_horario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_horario' => $id_horario]);
        $fila = $stmt->fetch();

        if ($fila === false) {
            return null;
        }

        return $this->mapearHorario($fila);
    }

    public function obtenerPorAreaYDia(int $id_area, int $dia_semana): ?HorarioFuncionamiento
    {
        $sql = 'SELECT * FROM horarios_funcionamiento WHERE id_area = :id_area AND dia_semana = :dia_semana AND activo = 1';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_area' => $id_area, ':dia_semana' => $dia_semana]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapearHorario($fila);
    }

    public function crear(HorarioFuncionamiento $horario): int
    {
        $sql = 'INSERT INTO horarios_funcionamiento (id_area, dia_semana, hora_apertura, hora_cierre, activo, fecha_creacion)
                VALUES (:id_area, :dia_semana, :hora_apertura, :hora_cierre, :activo, CURRENT_TIMESTAMP)';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':id_area' => $horario->id_area,
            ':dia_semana' => $horario->dia_semana,
            ':hora_apertura' => $horario->hora_apertura,
            ':hora_cierre' => $horario->hora_cierre,
            ':activo' => $horario->activo ? 1 : 0,
        ]);

        return (int) Conexion::obtener()->lastInsertId();
    }

    public function actualizar(HorarioFuncionamiento $horario): bool
    {
        $sql = 'UPDATE horarios_funcionamiento
                    SET hora_apertura = :hora_apertura, hora_cierre = :hora_cierre, activo = :activo,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id_horario = :id_horario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([
            ':hora_apertura' => $horario->hora_apertura,
            ':hora_cierre' => $horario->hora_cierre,
            ':activo' => $horario->activo ? 1 : 0,
            ':id_horario' => $horario->id_horario,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function desactivar(int $id_horario): bool
    {
        $sql = 'UPDATE horarios_funcionamiento SET activo = 0 WHERE id_horario = :id_horario';
        $stmt = Conexion::obtener()->prepare($sql);
        $stmt->execute([':id_horario' => $id_horario]);

        return $stmt->rowCount() > 0;
    }

    private function mapearHorario(array $fila): HorarioFuncionamiento
    {
        $horario = new HorarioFuncionamiento(
            (int) $fila['id_area'],
            (int) $fila['dia_semana'],
            $fila['hora_apertura'],
            $fila['hora_cierre']
        );
        $horario->id_horario = (int) $fila['id_horario'];
        $horario->activo = (bool) $fila['activo'];
        $horario->fecha_creacion = $fila['fecha_creacion'] ?? null;

        return $horario;
    }
}
