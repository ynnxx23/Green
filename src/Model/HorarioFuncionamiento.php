<?php

declare(strict_types=1);

namespace Restaurante\Model;

class HorarioFuncionamiento
{
    public ?int $id_horario = null;
    public int $id_area;
    public int $dia_semana;
    public string $hora_apertura;
    public string $hora_cierre;
    public bool $activo = true;
    public ?string $fecha_creacion = null;

    public function __construct(int $id_area, int $dia_semana, string $hora_apertura, string $hora_cierre)
    {
        $this->id_area = $id_area;
        $this->dia_semana = $dia_semana;
        $this->hora_apertura = $hora_apertura;
        $this->hora_cierre = $hora_cierre;
    }
}
