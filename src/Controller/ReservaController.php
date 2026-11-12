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

        // Crear reserva (POST en la raiz). Se evalua antes que el listado
        // para que un POST a /api/reservas no se interprete como consulta.
        if ($ruta_interna === '' && $metodo === 'POST') {
            return $this->crearReserva();
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

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function crearReserva(): array
    {
        // La reserva la puede crear un cliente sin sesion: se registra con los
        // datos que envia (nombre, email, telefono). Si hay sesion, se usan los
        // datos del usuario autenticado.
        $entrada = $this->leerCuerpoJson();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::estaAutenticado()
            ? \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado()
            : 0;
        $nombre = trim((string) ($entrada['nombre'] ?? ''));
        $email = trim(strtolower((string) ($entrada['email'] ?? '')));
        $telefono = trim((string) ($entrada['telefono'] ?? ''));
        $comensales = (int) ($entrada['comensales'] ?? 1);
        $fecha_reserva = (string) ($entrada['fecha_reserva'] ?? '');
        $hora_inicio = substr((string) ($entrada['hora_inicio'] ?? ''), 0, 5);
        $hora_fin = substr((string) ($entrada['hora_fin'] ?? ''), 0, 5);
        $ocasion = trim((string) ($entrada['ocasion'] ?? ''));
        $observaciones = trim((string) ($entrada['observaciones'] ?? ''));

        if ($id_usuario <= 0) {
            if ($nombre === '' || $email === '') {
                return $this->error('Faltan datos obligatorios: nombre, email, fecha_reserva y hora_inicio.', 400);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('El email no tiene un formato válido.', 400);
            }
        }

        if ($fecha_reserva === '' || $hora_inicio === '') {
            return $this->error('Faltan datos obligatorios: fecha_reserva y hora_inicio.', 400);
        }
        if ($comensales < 1) {
            $comensales = 1;
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        // -----------------------------------------------------------------
        // 1. Verificar disponibilidad del cupo (fecha + franja) y mesas libres
        // -----------------------------------------------------------------
        $stmt = $pdo->prepare('
            SELECT id_disponibilidad, mesas_disponibles, hora_inicio as disp_inicio, hora_fin as disp_fin
            FROM disponibilidad_mesa
            WHERE fecha = ? AND activo = 1
        ');
        $stmt->execute([$fecha_reserva]);
        $disponibilidad = null;
        while ($f = $stmt->fetch()) {
            if ($hora_inicio >= substr((string) $f['disp_inicio'], 0, 5)
                && $hora_inicio < substr((string) $f['disp_fin'], 0, 5)) {
                $disponibilidad = $f;
                break;
            }
        }

        if (!$disponibilidad) {
            return $this->error('No hay disponibilidad de mesas para la fecha y hora seleccionadas.', 400);
        }

        if ((int) $disponibilidad['mesas_disponibles'] <= 0) {
            return $this->error('No quedan mesas disponibles para esa fecha y horario.', 400);
        }

        // Si no se indico hora_fin, se usa el fin del cupo disponible.
        if ($hora_fin === '') {
            $hora_fin = substr((string) $disponibilidad['disp_fin'], 0, 5);
        }
        if ($hora_fin <= $hora_inicio) {
            $hora_fin = substr((string) $disponibilidad['disp_fin'], 0, 5);
        }

        // -----------------------------------------------------------------
        // 2. Asignar automaticamente una mesa libre con capacidad suficiente
        // -----------------------------------------------------------------
        $id_mesa = $this->buscarMesaLibre($pdo, $fecha_reserva, $hora_inicio, $hora_fin, $comensales);
        if ($id_mesa === null) {
            return $this->error('No hay mesas libres con capacidad suficiente para ese horario.', 400);
        }

        try {
            $pdo->beginTransaction();

            // Usuario de la sesion o usuario creado con los datos del formulario
            if ($id_usuario <= 0) {
                $id_usuario = $this->obtenerOCrearUsuario($pdo, $nombre, $email, $telefono);
            }

            // Decrementar mesas disponibles
            $stmt = $pdo->prepare('
                UPDATE disponibilidad_mesa SET mesas_disponibles = mesas_disponibles - 1
                WHERE id_disponibilidad = ? AND mesas_disponibles > 0
            ');
            $stmt->execute([$disponibilidad['id_disponibilidad']]);
            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Se agotaron las mesas disponibles para ese horario.');
            }

            $id_reserva = $this->servicioDeReservas->crearReserva(
                $id_mesa,
                $id_usuario,
                $fecha_reserva,
                $hora_inicio,
                $hora_fin,
                $comensales,
                $telefono,
                $ocasion,
                $observaciones
            );

            // Historial de movimiento
            $this->insertarHistorial($pdo, 'reservas', $id_reserva, 'CREAR_RESERVA', [
                'id_mesa' => $id_mesa,
                'fecha_reserva' => $fecha_reserva,
                'hora_inicio' => $hora_inicio,
                'hora_fin' => $hora_fin,
                'comensales' => $comensales
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
                    'comensales' => $comensales,
                    'estado' => 'confirmada',
                ],
                'mensaje' => '¡Reserva confirmada! Te esperamos en el restaurante.',
            ];
        } catch (\InvalidArgumentException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error($e->getMessage(), 409);
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('Error al crear la reserva. Intente nuevamente.', 500);
        }
    }

    /**
     * Busca una mesa activa, con capacidad suficiente y sin reservas que se
     * solapen con el rango horario indicado.
     */
    private function buscarMesaLibre(\PDO $pdo, string $fecha, string $hora_inicio, string $hora_fin, int $comensales): ?int
    {
        $stmt = $pdo->prepare('
            SELECT m.id_mesa
            FROM mesas m
            WHERE m.activo = 1
              AND m.capacidad >= :comensales
              AND NOT EXISTS (
                  SELECT 1 FROM reservas r
                  WHERE r.id_mesa = m.id_mesa
                    AND r.fecha_reserva = :fecha
                    AND r.estado IN (\'pendiente\', \'confirmada\')
                    AND r.hora_inicio < :hora_fin
                    AND r.hora_fin > :hora_inicio
              )
            ORDER BY m.capacidad ASC, m.id_mesa ASC
            LIMIT 1
        ');
        $stmt->execute([
            ':comensales' => $comensales,
            ':fecha' => $fecha,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
        ]);
        $fila = $stmt->fetch();

        return $fila === false ? null : (int) $fila['id_mesa'];
    }

    /**
     * Devuelve el id de un usuario existente con ese email o crea uno nuevo
     * (rol cliente) para poder asociarle la reserva.
     */
    private function obtenerOCrearUsuario(\PDO $pdo, string $nombre, string $email, string $telefono): int
    {
        $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existente = $stmt->fetch();
        if ($existente !== false) {
            $id = (int) $existente['id_usuario'];
            if ($telefono !== '') {
                $stmt = $pdo->prepare('UPDATE usuarios SET telefono = ? WHERE id_usuario = ?');
                $stmt->execute([$telefono, $id]);
            }
            return $id;
        }

        // Contrasena aleatoria: el usuario no inicia sesion desde la web publica.
        $contrasena = bin2hex(random_bytes(12)) . 'Aa1';

        $stmt = $pdo->prepare('
            INSERT INTO usuarios (nombre, email, contrasena_hash, telefono, id_rol, activo, fecha_creacion)
            VALUES (:nombre, :email, :hash, :tel, 1, 1, CURRENT_TIMESTAMP)
        ');
        $stmt->execute([
            ':nombre' => $nombre !== '' ? $nombre : 'Cliente',
            ':email' => $email,
            ':hash' => password_hash($contrasena, PASSWORD_BCRYPT),
            ':tel' => $telefono !== '' ? $telefono : null,
        ]);

        return (int) $pdo->lastInsertId();
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
            'comensales' => $reserva->comensales ?? 1,
            'telefono' => $reserva->telefono ?? null,
            'ocasion' => $reserva->ocasion ?? null,
            'observaciones' => $reserva->observaciones ?? null,
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
