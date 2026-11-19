<?php

declare(strict_types=1);

namespace Restaurante\Validator;

use Restaurante\Dao\ReservaDao;
use Restaurante\Dao\MesaDao;
use Restaurante\Database\Conexion;

class ValidadorDeReserva
{
    private ReservaDao $reservaDao;
    private MesaDao $mesaDao;

    private const MAX_ANTICIPACION_DIAS = 30;

    public function __construct(ReservaDao $reservaDao, MesaDao $mesaDao)
    {
        $this->reservaDao = $reservaDao;
        $this->mesaDao = $mesaDao;
    }

    public function validarCreacion(
        int $id_mesa,
        int $id_usuario,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin,
        ?int $id_reserva_excluir = null
    ): void {
        $this->validarFormatoFecha($fecha_reserva);
        $this->validarFormatoHora($hora_inicio);
        $this->validarFormatoHora($hora_fin);
        $this->validarHoraFinMayorQueInicio($hora_inicio, $hora_fin);
        $this->validarNoPasado($fecha_reserva, $hora_inicio);
        $this->validarAnticipacionMaxima($fecha_reserva);
        $this->validarSinSolapamiento($id_mesa, $fecha_reserva, $hora_inicio, $hora_fin, $id_reserva_excluir);
    }

    public function validarCancelacion(array $reserva): void
    {
        $estado = $reserva['estado'] ?? '';
        if (!in_array($estado, ['pendiente', 'confirmada'], true)) {
            throw new \InvalidArgumentException('Solo se pueden cancelar reservas pendientes o confirmadas.');
        }

        $ahora = new \DateTime('now');
        $fechaHoraFin = new \DateTime($reserva['fecha_reserva'] . ' ' . $reserva['hora_fin']);
        if ($fechaHoraFin <= $ahora) {
            throw new \InvalidArgumentException('No se puede cancelar una reserva que ya finalizó.');
        }
    }

    public function validarEstado(string $estado): void
    {
        $validos = ['pendiente', 'confirmada', 'cancelada', 'vencida', 'completada'];
        if (!in_array($estado, $validos, true)) {
            throw new \InvalidArgumentException('Estado de reserva no válido.');
        }
    }

    private function validarFormatoFecha(string $fecha): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('La fecha debe tener formato YYYY-MM-DD.');
        }

        $date = \DateTime::createFromFormat('Y-m-d', $fecha);
        if ($date === false || $date->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('La fecha no es válida.');
        }
    }

    private function validarFormatoHora(string $hora): void
    {
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            throw new \InvalidArgumentException('La hora debe tener formato HH:MM o HH:MM:SS.');
        }
    }

    private function validarHoraFinMayorQueInicio(string $hora_inicio, string $hora_fin): void
    {
        $inicio = strtotime('1970-01-01 ' . $hora_inicio);
        $fin = strtotime('1970-01-01 ' . $hora_fin);

        if ($inicio === false || $fin === false) {
            throw new \InvalidArgumentException('Formato de hora inválido.');
        }

        if ($fin <= $inicio) {
            throw new \InvalidArgumentException('La hora de fin debe ser posterior a la hora de inicio.');
        }
    }

    private function validarNoPasado(string $fecha_reserva, string $hora_inicio): void
    {
        $fechaHora = new \DateTime($fecha_reserva . ' ' . $hora_inicio);
        $ahora = new \DateTime('now');

        if ($fechaHora <= $ahora) {
            throw new \InvalidArgumentException('No se puede reservar en una fecha y hora ya pasadas.');
        }
    }

    private function validarAnticipacionMaxima(string $fecha_reserva): void
    {
        $fechaReserva = new \DateTime($fecha_reserva);
        $fechaMaxima = (new \DateTime('now'))->modify('+' . self::MAX_ANTICIPACION_DIAS . ' days');
        $fechaMaxima->setTime(23, 59, 59);

        if ($fechaReserva > $fechaMaxima) {
            throw new \InvalidArgumentException(
                'No se puede reservar con más de ' . self::MAX_ANTICIPACION_DIAS . ' días de anticipación.'
            );
        }
    }

    private function validarSinSolapamiento(
        int $id_mesa,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin,
        ?int $id_reserva_excluir = null
    ): void {
        if ($this->reservaDao->existeSolapamiento(
            $id_mesa,
            $fecha_reserva,
            $hora_inicio,
            $hora_fin,
            $id_reserva_excluir
        )) {
            throw new \InvalidArgumentException(
                'La mesa ya tiene una reserva confirmada en ese horario. Elija otro horario.'
            );
        }
    }
}
