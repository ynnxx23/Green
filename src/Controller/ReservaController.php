<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeReservas;

class ReservaController
{
    private ServicioDeReservas $servicioDeReservas;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDeReservas $servicioDeReservas, AuthMiddleware $authMiddleware)
    {
        $this->servicioDeReservas = $servicioDeReservas;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        if ($ruta_interna === 'mis-reservas') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->misReservas();
        }

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->listarTodas();
        }

        if (preg_match('/^(\d+)\/(.*)$/', $ruta_interna, $coincidencias)) {
            $id_reserva = (int) $coincidencias[1];
            $subruta = $coincidencias[2];

            if ($subruta === 'cancelar') {
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->cancelarReserva($id_reserva);
            }

            if ($subruta === 'estado') {
                if ($metodo !== 'POST') {
                    return $this->error('Método no permitido.', 405);
                }
                return $this->cambiarEstado($id_reserva);
            }
        }

        if ($ruta_interna === '' && $metodo === 'POST') {
            return $this->crearReserva();
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function crearReserva(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $entrada = $this->leerCuerpoJson();

        $id_mesa = (int) ($entrada['id_mesa'] ?? 0);
        $fecha_reserva = $entrada['fecha_reserva'] ?? '';
        $hora_inicio = $entrada['hora_inicio'] ?? '';
        $hora_fin = $entrada['hora_fin'] ?? '';
        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

        if ($id_mesa <= 0 || $fecha_reserva === '' || $hora_inicio === '' || $hora_fin === '') {
            return $this->error('Faltan datos obligatorios: id_mesa, fecha_reserva, hora_inicio, hora_fin.', 400);
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        // 1. Verificar que exista disponibilidad para la fecha y el rango horario cubra la reserva
        $stmt = $pdo->prepare('
            SELECT id_disponibilidad, mesas_disponibles, hora_inicio as disp_inicio, hora_fin as disp_fin
            FROM disponibilidad_mesa
            WHERE fecha = ? AND activo = 1
        ');
        $stmt->execute([$fecha_reserva]);
        $disponibilidad = $stmt->fetch();

        if (!$disponibilidad) {
            return $this->error('No hay disponibilidad de mesas para la fecha seleccionada.', 400);
        }

        if ((int) $disponibilidad['mesas_disponibles'] <= 0) {
            return $this->error('No quedan mesas disponibles para esa fecha y horario.', 400);
        }

        // Verificar que la hora de la reserva esté dentro del rango de disponibilidad
        $disp_inicio = $disponibilidad['disp_inicio'];
        $disp_fin = $disponibilidad['disp_fin'];
        if ($hora_inicio < $disp_inicio || $hora_fin > $disp_fin) {
            return $this->error('El horario seleccionado está fuera del rango de disponibilidad.', 400);
        }

        // 2. Verificar que no haya solapamiento con otra reserva de la misma mesa
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as solapamientos
            FROM reservas
            WHERE id_mesa = ?
              AND fecha_reserva = ?
              AND estado IN (\"pendiente\", \"confirmada\")
              AND hora_inicio < ?
              AND hora_fin > ?
        ');
        $stmt->execute([$id_mesa, $fecha_reserva, $hora_fin, $hora_inicio]);
        $solapamientos = (int) $stmt->fetchColumn();

        if ($solapamientos > 0) {
            return $this->error('La mesa ya tiene una reserva en ese horario.', 400);
        }

        try {
            $pdo->beginTransaction();

            // Decrementar mesas disponibles
            $stmt = $pdo->prepare('
                UPDATE disponibilidad_mesa SET mesas_disponibles = mesas_disponibles - 1
                WHERE id_disponibilidad = ?
            ');
            $stmt->execute([$disponibilidad['id_disponibilidad']]);

            $id_reserva = $this->servicioDeReservas->crearReserva(
                $id_mesa,
                $id_usuario,
                $fecha_reserva,
                $hora_inicio,
                $hora_fin
            );

            // Historial de movimiento
            $this->insertarHistorial($pdo, 'reservas', $id_reserva, 'CREAR_RESERVA', [
                'id_mesa' => $id_mesa,
                'fecha_reserva' => $fecha_reserva,
                'hora_inicio' => $hora_inicio,
                'hora_fin' => $hora_fin
            ], $id_usuario);

            $pdo->commit();

            return [
                'exito' => true,
                'datos' => [
                    'id_reserva' => $id_reserva,
                    'id_mesa' => $id_mesa,
                    'fecha_reserva' => $fecha_reserva,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'estado' => 'pendiente',
                ],
                'mensaje' => 'Reserva creada correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            $pdo->rollBack();
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return $this->error('Error al crear la reserva. Intente nuevamente.', 500);
        }
    }

    private function misReservas(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();
        $reservas = $this->servicioDeReservas->listarReservasUsuario($id_usuario);

        $datos = [];
        foreach ($reservas as $reserva) {
            $datos[] = $this->mapearReserva($reserva);
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Reservas obtenidas correctamente.',
        ];
    }

    private function cancelarReserva(int $id_reserva): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

        try {
            $resultado = $this->servicioDeReservas->cancelarReserva($id_reserva, $id_usuario);

            if (!$resultado) {
                return $this->error('No se pudo cancelar la reserva.', 400);
            }

            // Historial de movimiento
            $pdo = \Restaurante\Database\Conexion::obtener();
            $this->insertarHistorial($pdo, 'reservas', $id_reserva, 'CANCELAR_RESERVA', [
                'id_reserva' => $id_reserva
            ], $id_usuario);

            return [
                'exito' => true,
                'mensaje' => 'Reserva cancelada correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al cancelar la reserva.', 500);
        }
    }

    private function listarTodas(): array
    {
        $this->authMiddleware->requiereAdministrador();

        $reservas = $this->servicioDeReservas->listarTodasLasReservas();

        $datos = [];
        foreach ($reservas as $reserva) {
            $datos[] = $this->mapearReserva($reserva, true);
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Todas las reservas obtenidas correctamente.',
        ];
    }

    private function cambiarEstado(int $id_reserva): array
    {
        $this->authMiddleware->requiereAdministrador();

        $entrada = $this->leerCuerpoJson();
        $estado = $entrada['estado'] ?? '';

        if ($estado === '') {
            return $this->error('Se requiere el parámetro estado.', 400);
        }

        try {
            $resultado = $this->servicioDeReservas->cambiarEstadoReserva($id_reserva, $estado);

            if (!$resultado) {
                return $this->error('No se pudo cambiar el estado de la reserva.', 400);
            }

            // Historial de movimiento
            $pdo = \Restaurante\Database\Conexion::obtener();
            $this->insertarHistorial($pdo, 'reservas', $id_reserva, 'CAMBIAR_ESTADO_RESERVA', [
                'id_reserva' => $id_reserva,
                'nuevo_estado' => $estado
            ], \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado());

            return [
                'exito' => true,
                'mensaje' => 'Estado de reserva actualizado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al cambiar el estado de la reserva.', 500);
        }
    }

    private function insertarHistorial(\PDO $pdo, string $tabla, ?int $id_registro, string $accion, array $detalles, ?int $id_usuario): void
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmt = $pdo->prepare('
                INSERT INTO historial_movimientos (tabla_afectada, id_registro, accion, detalles, id_usuario, direccion_ip)
                VALUES (:ta, :ir, :ac, :de, :iu, :ip)
            ');
            $stmt->execute([
                ':ta' => $tabla,
                ':ir' => $id_registro,
                ':ac' => $accion,
                ':de' => json_encode($detalles),
                ':iu' => $id_usuario,
                ':ip' => $ip,
            ]);
        } catch (\PDOException $e) {
            // No fallar la operación principal por un error de historial
            error_log('Error insertando historial: ' . $e->getMessage());
        }
    }

    private function leerCuerpoJson(): array
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
    }

    private function error(string $mensaje, int $codigo_http): array
    {
        http_response_code($codigo_http);

        return [
            'exito' => false,
            'datos' => null,
            'mensaje' => $mensaje,
            'codigo' => $codigo_http,
        ];
    }

    private function mapearReserva(object $reserva, bool $incluirUsuario = false): array
    {
        $datos = [
            'id_reserva' => $reserva->id_reserva,
            'id_mesa' => $reserva->id_mesa,
            'id_usuario' => $reserva->id_usuario,
            'fecha_reserva' => $reserva->fecha_reserva,
            'hora_inicio' => $reserva->hora_inicio,
            'hora_fin' => $reserva->hora_fin,
            'estado' => $reserva->estado,
            'fecha_creacion' => $reserva->fecha_creacion,
            'fecha_actualizacion' => $reserva->fecha_actualizacion,
        ];

        if ($incluirUsuario) {
            $datos['nombre_usuario'] = $reserva->nombre_usuario ?? null;
            $datos['email_usuario'] = $reserva->email_usuario ?? null;
            $datos['numero_mesa'] = $reserva->numero_mesa ?? null;
        }

        return $datos;
    }
}
