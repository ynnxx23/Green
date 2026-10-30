<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDePedidos;

class PedidoController
{
    private ServicioDePedidos $servicioDePedidos;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDePedidos $servicioDePedidos, AuthMiddleware $authMiddleware)
    {
        $this->servicioDePedidos = $servicioDePedidos;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        if ($ruta_interna === 'mis-pedidos') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->misPedidos();
        }

        if (preg_match('/^estado\/(.+)$/', $ruta_interna, $coincidencias)) {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->porEstado($coincidencias[1]);
        }

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo === 'GET') {
                return $this->listarTodos();
            }
            if ($metodo === 'POST') {
                return $this->registrarPedido();
            }
            return $this->error('Método no permitido.', 405);
        }

        if (preg_match('/^(\d+)\/estado$/', $ruta_interna, $coincidencias)) {
            if ($metodo !== 'POST') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->cambiarEstado((int) $coincidencias[1]);
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function registrarPedido(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $entrada = $this->leerCuerpoJson();

        $id_mesa = isset($entrada['id_mesa']) && $entrada['id_mesa'] !== '' ? (int) $entrada['id_mesa'] : null;
        $detalles = $entrada['detalles'] ?? [];
        $tipo_pedido = $entrada['tipo_pedido'] ?? 'con_reserva';
        $seña = isset($entrada['seña']) ? (float) $entrada['seña'] : 0.00;
        $estado_seña = $entrada['estado_seña'] ?? 'pendiente';
        $direccion_domicilio = $entrada['direccion_domicilio'] ?? null;
        $id_zona_delivery = isset($entrada['id_zona_delivery']) && $entrada['id_zona_delivery'] !== '' ? (int) $entrada['id_zona_delivery'] : null;
        $costo_envio = isset($entrada['costo_envio']) ? (float) $entrada['costo_envio'] : 0.00;
        $id_repartidor = isset($entrada['id_repartidor']) && $entrada['id_repartidor'] !== '' ? (int) $entrada['id_repartidor'] : null;
        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();
        $id_mesero = $id_usuario;

        if (!is_array($detalles) || count($detalles) === 0) {
            return $this->error('Se requiere al menos un producto en el pedido.', 400);
        }

        if (!in_array($tipo_pedido, ['con_reserva', 'para_llevar', 'domicilio'], true)) {
            return $this->error('Tipo de pedido no válido.', 400);
        }

        if ($tipo_pedido === 'con_reserva' && ($id_mesa === null || $id_mesa <= 0)) {
            return $this->error('Se requiere id_mesa para pedidos con reserva.', 400);
        }

        if ($tipo_pedido === 'domicilio' && empty($direccion_domicilio)) {
            return $this->error('Se requiere dirección de domicilio para pedidos a domicilio.', 400);
        }

        // -----------------------------------------------------------------
        // RF-70: Bloquear automáticamente pedidos sin reserva cuando se alcanza el límite
        // -----------------------------------------------------------------
        if ($tipo_pedido === 'para_llevar' || $tipo_pedido === 'domicilio') {
            $pdo = \Restaurante\Database\Conexion::obtener();
            $fecha = date('Y-m-d');
            $tipoContador = $tipo_pedido === 'para_llevar' ? 'para_llevar' : 'domicilio';

            $stmt = $pdo->prepare('
                SELECT c.valor AS limite
                FROM configuracion_sistema c
                WHERE c.clave = ?
            ');
            $clave = $tipo_pedido === 'para_llevar' ? 'limite_pedidos_sin_reserva_retiro' : 'limite_pedidos_sin_reserva_domicilio';
            $stmt->execute([$clave]);
            $config = $stmt->fetch();
            $limite = $config ? (int) $config['limite'] : 0;

            if ($limite > 0) {
                $stmt = $pdo->prepare('
                    SELECT cantidad_pedidos FROM contador_pedidos_sin_reserva WHERE fecha = ? AND tipo_pedido = ?
                ');
                $stmt->execute([$fecha, $tipoContador]);
                $contador = $stmt->fetch();
                $cantidadActual = $contador ? (int) $contador['cantidad_pedidos'] : 0;

                if ($cantidadActual >= $limite) {
                    return $this->error('Se ha alcanzado el límite máximo de pedidos sin reserva para ' . ($tipo_pedido === 'para_llevar' ? 'retiro' : 'delivery') . ' hoy. Intente con una reserva de mesa o en otro horario.', 403);
                }
            }
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        // Validar stock y límite de venta por producto
        $total_pedido = 0;
        foreach ($detalles as $detalle) {
            $id_producto = (int) ($detalle['id_producto'] ?? 0);
            $cantidad = (int) ($detalle['cantidad'] ?? 0);
            $precio_unitario = (float) ($detalle['precio_unitario'] ?? 0);

            if ($id_producto <= 0 || $cantidad <= 0 || $precio_unitario < 0) {
                return $this->error('Datos de producto inválidos en el pedido.', 400);
            }

            // Verificar stock existente
            $stmt = $pdo->prepare('SELECT stock_actual, limite_venta FROM stock_producto WHERE id_producto = ? AND activo = 1');
            $stmt->execute([$id_producto]);
            $stock = $stmt->fetch();

            if (!$stock) {
                return $this->error('El producto no tiene stock configurado.', 400);
            }

            if ((int) $stock['stock_actual'] < $cantidad) {
                return $this->error('Stock insuficiente para el producto id ' . $id_producto . '.', 400);
            }

            // Validar límite de venta (0 = sin límite)
            if ((int) $stock['limite_venta'] > 0) {
                $stmt2 = $pdo->prepare('
                    SELECT COALESCE(SUM(dp.cantidad), 0) as vendidos
                    FROM detalle_pedidos dp
                    JOIN pedidos p ON p.id_pedido = dp.id_pedido
                    WHERE dp.id_producto = ? AND p.estado IN (\'pendiente\', \'en_preparacion\')
                ');
                $stmt2->execute([$id_producto]);
                $vendidos = (int) $stmt2->fetchColumn();

                if ($vendidos + $cantidad > (int) $stock['limite_venta']) {
                    return $this->error('Se excede el límite de venta para el producto id ' . $id_producto . '.', 400);
                }
            }

            $total_pedido += $cantidad * $precio_unitario;
        }

        try {
            $pdo->beginTransaction();

            // Registrar pedido
            $stmt = $pdo->prepare('
                INSERT INTO pedidos (id_mesa, id_mesero, id_usuario, fecha_pedido, estado, total, tipo_pedido, seña, estado_seña, direccion_domicilio, id_zona_delivery, costo_envio, id_repartidor, fecha_pedido_hora, fecha_creacion)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP, \"pendiente\", ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ');
            $stmt->execute([
                $id_mesa,
                $id_mesero,
                $id_usuario,
                $total_pedido,
                $tipo_pedido,
                $seña,
                $estado_seña,
                $direccion_domicilio,
                $id_zona_delivery,
                $costo_envio,
                $id_repartidor,
            ]);
            $id_pedido = (int) $pdo->lastInsertId();

            // Insertar detalles y descontar stock
            foreach ($detalles as $detalle) {
                $id_producto = (int) ($detalle['id_producto'] ?? 0);
                $cantidad = (int) ($detalle['cantidad'] ?? 0);
                $precio_unitario = (float) ($detalle['precio_unitario'] ?? 0);

                $stmt = $pdo->prepare('
                    INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio_unitario]);

                // Descontar stock
                $stmt = $pdo->prepare('
                    UPDATE stock_producto SET stock_actual = stock_actual - ?, fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_producto = ?
                ');
                $stmt->execute([$cantidad, $id_producto]);
            }

            $pdo->commit();

            // Historial de movimiento
            $this->insertarHistorial($pdo, 'pedidos', $id_pedido, 'CREAR_PEDIDO', [
                'id_mesa' => $id_mesa,
                'total' => $total_pedido,
                'tipo_pedido' => $entrada['tipo_pedido'] ?? 'con_reserva',
                'seña' => $entrada['seña'] ?? 0
            ], $id_mesero);

            // RF-69: Contabilizar pedidos sin reserva por día
            if ($tipo_pedido === 'para_llevar' || $tipo_pedido === 'domicilio') {
                $tipoContador = $tipo_pedido === 'para_llevar' ? 'para_llevar' : 'domicilio';
                $stmtContador = $pdo->prepare('
                    INSERT INTO contador_pedidos_sin_reserva (fecha, tipo_pedido, cantidad_pedidos)
                    VALUES (:f, :t, 1)
                    ON DUPLICATE KEY UPDATE cantidad_pedidos = cantidad_pedidos + 1, fecha_actualizacion = CURRENT_TIMESTAMP
                ');
                $stmtContador->execute([
                    ':f' => date('Y-m-d'),
                    ':t' => $tipoContador,
                ]);
            }

            return [
                'exito' => true,
                'datos' => [
                    'id_pedido' => $id_pedido,
                    'id_mesa' => $id_mesa,
                    'estado' => 'pendiente',
                    'total' => $total_pedido,
                ],
                'mensaje' => 'Pedido registrado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            $pdo->rollBack();
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return $this->error('Error al registrar el pedido. Intente nuevamente.', 500);
        }
    }

    private function misPedidos(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_mesero = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();
        $pedidos = $this->servicioDePedidos->listarPedidosMesero($id_mesero);

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => 'Pedidos obtenidos correctamente.',
        ];
    }

    private function listarTodos(): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $pedidos = $this->servicioDePedidos->listarTodosLosPedidos();

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => 'Todos los pedidos obtenidos correctamente.',
        ];
    }

    private function porEstado(string $estado): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $validos = ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
        if (!in_array($estado, $validos, true)) {
            return $this->error('Estado no válido.', 400);
        }

        $pedidos = $this->servicioDePedidos->listarPedidosPorEstado($estado);

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => "Pedidos con estado '{$estado}' obtenidos correctamente.",
        ];
    }

    private function cambiarEstado(int $id_pedido): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $entrada = $this->leerCuerpoJson();
        $estado_nuevo = $entrada['estado'] ?? '';

        if ($estado_nuevo === '') {
            return $this->error('Se requiere el parámetro estado.', 400);
        }

        try {
            $resultado = $this->servicioDePedidos->cambiarEstadoPedido($id_pedido, $estado_nuevo);

            if (!$resultado) {
                return $this->error('No se pudo cambiar el estado del pedido.', 400);
            }

            // Historial de movimiento
            $pdo = \Restaurante\Database\Conexion::obtener();
            $this->insertarHistorial($pdo, 'pedidos', $id_pedido, 'CAMBIAR_ESTADO_PEDIDO', [
                'id_pedido' => $id_pedido,
                'nuevo_estado' => $estado_nuevo
            ], \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado());

            return [
                'exito' => true,
                'mensaje' => "Estado del pedido cambiado a '{$estado_nuevo}'.",
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al cambiar el estado del pedido.', 500);
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

    private function mapearPedidos(array $pedidos): array
    {
        $resultado = [];
        foreach ($pedidos as $pedido) {
            $resultado[] = [
                'id_pedido' => (int) $pedido['id_pedido'],
                'id_mesa' => $pedido['id_mesa'] !== null ? (int) $pedido['id_mesa'] : null,
                'numero_mesa' => $pedido['numero_mesa'] ?? null,
                'id_mesero' => $pedido['id_mesero'] !== null ? (int) $pedido['id_mesero'] : null,
                'nombre_mesero' => $pedido['nombre_mesero'] ?? null,
                'id_zona_delivery' => $pedido['id_zona_delivery'] !== null ? (int) $pedido['id_zona_delivery'] : null,
                'costo_envio' => (float) ($pedido['costo_envio'] ?? 0),
                'id_repartidor' => $pedido['id_repartidor'] !== null ? (int) $pedido['id_repartidor'] : null,
                'fecha_pedido' => $pedido['fecha_pedido'] ?? null,
                'estado' => $pedido['estado'],
                'total' => (float) $pedido['total'],
                'fecha_actualizacion' => $pedido['fecha_actualizacion'] ?? null,
            ];
        }

        return $resultado;
    }
}
