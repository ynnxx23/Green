<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\HorarioFuncionamientoDao;
use Restaurante\Model\HorarioFuncionamiento;

class ServicioDeHorarios
{
    private HorarioFuncionamientoDao $horarioDao;

    public function __construct(HorarioFuncionamientoDao $horarioDao)
    {
        $this->horarioDao = $horarioDao;
    }

    public function listarPorArea(int $id_area): array
    {
        return $this->horarioDao->listarPorArea($id_area);
    }

    public function obtenerPorId(int $id_horario): ?HorarioFuncionamiento
    {
        return $this->horarioDao->obtenerPorId($id_horario);
    }

    public function crearHorario(int $id_area, int $dia_semana, string $hora_apertura, string $hora_cierre): int
    {
        if ($dia_semana < 0 || $dia_semana > 6) {
            throw new \InvalidArgumentException('Día de la semana inválido (0=domingo, 6=sábado).');
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_apertura)
            || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_cierre)) {
            throw new \InvalidArgumentException('Las horas deben tener formato HH:MM o HH:MM:SS.');
        }

        $apertura = strtotime('1970-01-01 ' . $hora_apertura);
        $cierre = strtotime('1970-01-01 ' . $hora_cierre);

        if ($apertura === false || $cierre === false) {
            throw new \InvalidArgumentException('Formato de hora inválido.');
        }

        if ($cierre <= $apertura) {
            throw new \InvalidArgumentException('La hora de cierre debe ser posterior a la hora de apertura.');
        }

        $existente = $this->horarioDao->obtenerPorAreaYDia($id_area, $dia_semana);
        if ($existente !== null) {
            throw new \InvalidArgumentException('Ya existe un horario para este área y día de la semana.');
        }

        $horario = new HorarioFuncionamiento($id_area, $dia_semana, $hora_apertura, $hora_cierre);
        return $this->horarioDao->crear($horario);
    }

    public function editarHorario(int $id_horario, string $hora_apertura, string $hora_cierre): bool
    {
        $horario = $this->horarioDao->obtenerPorId($id_horario);
        if ($horario === null) {
            throw new \InvalidArgumentException('El horario no existe.');
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_apertura)
            || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_cierre)) {
            throw new \InvalidArgumentException('Las horas deben tener formato HH:MM o HH:MM:SS.');
        }

        $apertura = strtotime('1970-01-01 ' . $hora_apertura);
        $cierre = strtotime('1970-01-01 ' . $hora_cierre);

        if ($apertura === false || $cierre === false) {
            throw new \InvalidArgumentException('Formato de hora inválido.');
        }

        if ($cierre <= $apertura) {
            throw new \InvalidArgumentException('La hora de cierre debe ser posterior a la hora de apertura.');
        }

        $horario->hora_apertura = $hora_apertura;
        $horario->hora_cierre = $hora_cierre;

        return $this->horarioDao->actualizar($horario);
    }

    public function desactivarHorario(int $id_horario): bool
    {
        $horario = $this->horarioDao->obtenerPorId($id_horario);
        if ($horario === null) {
            throw new \InvalidArgumentException('El horario no existe.');
        }

        return $this->horarioDao->desactivar($id_horario);
    }
}
