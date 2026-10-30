<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Database\Conexion;

class ServicioDeDisponibilidad
{
    public function calcularDisponibilidad(int $id_mesa, string $fecha_reserva): array
    {
        $pdo = Conexion::obtener();

        $mesa = $this->obtenerMesa($pdo, $id_mesa);
        if ($mesa === null) {
            return [];
        }

        if (!$mesa['activo']) {
            return [];
        }

        $id_area = (int) $mesa['id_area'];
        $dia_semana = (int) date('w', strtotime($fecha_reserva));

        $horario = $this->obtenerHorarioFuncionamiento($pdo, $id_area, $dia_semana);
        if ($horario === null) {
            return [];
        }

        $hora_apertura = $horario['hora_apertura'];
        $hora_cierre = $horario['hora_cierre'];
        $duracion_turno = 60;

        $slots_candidatos = $this->generarSlots($hora_apertura, $hora_cierre, $duracion_turno);

        $reservas_ocupadas = $this->obtenerReservasOcupadas($pdo, $id_mesa, $fecha_reserva);
        $bloques = $this->obtenerBloques($pdo, $id_mesa, $fecha_reserva);

        $disponibles = [];
        foreach ($slots_candidatos as $slot) {
            if (!$this->estaSuperpuestoConReservas($slot, $reservas_ocupadas)
                && !$this->estaSuperpuestoConBloques($slot, $bloques)) {
                $disponibles[] = $slot;
            }
        }

        return $disponibles;
    }

    public function existeReservaSolapada(
        int $id_mesa,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin,
        ?int $id_reserva_excluir = null
    ): bool {
        $pdo = Conexion::obtener();

        $sql = 'SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_mesa = :id_mesa
                  AND fecha_reserva = :fecha_reserva
                  AND estado IN (\'pendiente\', \'confirmada\')
                  AND hora_inicio < :hora_fin
                  AND hora_fin > :hora_inicio';

        if ($id_reserva_excluir !== null) {
            $sql .= ' AND id_reserva <> :id_reserva_excluir';
        }

        $stmt = $pdo->prepare($sql);
        $params = [
            ':id_mesa' => $id_mesa,
            ':fecha_reserva' => $fecha_reserva,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
        ];

        if ($id_reserva_excluir !== null) {
            $params[':id_reserva_excluir'] = $id_reserva_excluir;
        }

        $stmt->execute($params);
        $fila = $stmt->fetch();

        return (int) $fila['total'] > 0;
    }

    private function obtenerMesa(\PDO $pdo, int $id_mesa): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM mesas WHERE id_mesa = :id_mesa');
        $stmt->execute([':id_mesa' => $id_mesa]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    private function obtenerHorarioFuncionamiento(\PDO $pdo, int $id_area, int $dia_semana): ?array
    {
        $stmt = $pdo->prepare('
            SELECT * FROM horarios_funcionamiento
            WHERE id_area = :id_area
              AND dia_semana = :dia_semana
              AND activo = 1
        ');
        $stmt->execute([
            ':id_area' => $id_area,
            ':dia_semana' => $dia_semana,
        ]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    private function generarSlots(string $hora_apertura, string $hora_cierre, int $duracion_minutos): array
    {
        $slots = [];
        $inicio = strtotime('1970-01-01 ' . $hora_apertura);
        $fin = strtotime('1970-01-01 ' . $hora_cierre);
        $duracion_segundos = $duracion_minutos * 60;

        $actual = $inicio;
        while ($actual + $duracion_segundos <= $fin) {
            $slots[] = [
                'hora_inicio' => date('H:i:s', $actual),
                'hora_fin' => date('H:i:s', $actual + $duracion_segundos),
            ];
            $actual += $duracion_segundos;
        }

        return $slots;
    }

    private function obtenerReservasOcupadas(\PDO $pdo, int $id_mesa, string $fecha_reserva): array
    {
        $stmt = $pdo->prepare('
            SELECT hora_inicio, hora_fin, estado
            FROM reservas
            WHERE id_mesa = :id_mesa
              AND fecha_reserva = :fecha_reserva
              AND estado IN (\'pendiente\', \'confirmada\')
        ');
        $stmt->execute([
            ':id_mesa' => $id_mesa,
            ':fecha_reserva' => $fecha_reserva,
        ]);

        return $stmt->fetchAll();
    }

    private function obtenerBloques(\PDO $pdo, int $id_mesa, string $fecha_reserva): array
    {
        $stmt = $pdo->prepare('
            SELECT fecha_inicio, fecha_fin
            FROM bloques_mesa
            WHERE id_mesa = :id_mesa
              AND DATE(fecha_inicio) <= :fecha_reserva
              AND DATE(fecha_fin) >= :fecha_reserva
        ');
        $stmt->execute([
            ':id_mesa' => $id_mesa,
            ':fecha_reserva' => $fecha_reserva,
        ]);

        return $stmt->fetchAll();
    }

    private function estaSuperpuestoConReservas(array $slot, array $reservas): bool
    {
        foreach ($reservas as $reserva) {
            if ($this->intervalosSeSolapan(
                $slot['hora_inicio'], $slot['hora_fin'],
                $reserva['hora_inicio'], $reserva['hora_fin']
            )) {
                return true;
            }
        }

        return false;
    }

    private function estaSuperpuestoConBloques(array $slot, array $bloques): bool
    {
        foreach ($bloques as $bloque) {
            $bloque_inicio = date('H:i:s', strtotime($bloque['fecha_inicio']));
            $bloque_fin = date('H:i:s', strtotime($bloque['fecha_fin']));

            if ($this->intervalosSeSolapan($slot['hora_inicio'], $slot['hora_fin'], $bloque_inicio, $bloque_fin)) {
                return true;
            }
        }

        return false;
    }

    private function intervalosSeSolapan(string $inicio1, string $fin1, string $inicio2, string $fin2): bool
    {
        return $inicio1 < $fin2 && $fin1 > $inicio2;
    }
}
