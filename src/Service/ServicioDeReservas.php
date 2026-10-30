<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\ReservaDao;
use Restaurante\Model\Reserva;
use Restaurante\Validator\ValidadorDeReserva;

class ServicioDeReservas
{
    private ReservaDao $reservaDao;
    private ValidadorDeReserva $validador;

    public function __construct(ReservaDao $reservaDao, ValidadorDeReserva $validador)
    {
        $this->reservaDao = $reservaDao;
        $this->validador = $validador;
    }

    public function crearReserva(
        int $id_mesa,
        int $id_usuario,
        string $fecha_reserva,
        string $hora_inicio,
        string $hora_fin
    ): int {
        $this->validador->validarCreacion(
            $id_mesa,
            $id_usuario,
            $fecha_reserva,
            $hora_inicio,
            $hora_fin
        );

        $reserva = new Reserva($id_mesa, $id_usuario, $fecha_reserva, $hora_inicio, $hora_fin, 'pendiente');

        $id_reserva = $this->reservaDao->crearConBloqueo($reserva);
        $reserva->id_reserva = $id_reserva;

        return $id_reserva;
    }

    public function obtenerReserva(int $id_reserva): ?Reserva
    {
        return $this->reservaDao->obtenerPorId($id_reserva);
    }

    public function listarReservasUsuario(int $id_usuario): array
    {
        return $this->reservaDao->listarPorUsuario($id_usuario);
    }

    public function listarTodasLasReservas(): array
    {
        return $this->reservaDao->listarTodas();
    }

    public function cancelarReserva(int $id_reserva, int $id_usuario): bool
    {
        $reserva = $this->reservaDao->obtenerPorId($id_reserva);

        if ($reserva === null) {
            throw new \InvalidArgumentException('La reserva no existe.');
        }

        if ($reserva->id_usuario !== $id_usuario) {
            throw new \InvalidArgumentException('No puede cancelar una reserva de otro usuario.');
        }

        $this->validador->validarCancelacion([
            'estado' => $reserva->estado,
            'fecha_reserva' => $reserva->fecha_reserva,
            'hora_fin' => $reserva->hora_fin,
        ]);

        return $this->reservaDao->cancelar($id_reserva);
    }

    public function cambiarEstadoReserva(int $id_reserva, string $estado): bool
    {
        $this->validador->validarEstado($estado);

        $reserva = $this->reservaDao->obtenerPorId($id_reserva);
        if ($reserva === null) {
            throw new \InvalidArgumentException('La reserva no existe.');
        }

        return $this->reservaDao->cambiarEstado($id_reserva, $estado);
    }
}
