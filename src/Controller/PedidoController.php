<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;

class PedidoController
{
    private AuthMiddleware $authMiddleware;

    public function __construct(AuthMiddleware $authMiddleware)
    {
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

        if ($ruta_interna === 'mis-pedidos-detalle') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->misPedidosConDetalle();
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

        if (preg_match('/^(\d+)$/', $ruta_interna, $coincidencias)) {
            $id_pedido = (int) $coincidencias[1];

            if ($metodo === 'GET') {
                return $this->obtenerDetalle($id_pedido);
            }

            if ($metodo === 'POST' && str_ends_with($ruta_interna, '/estado')) {
                return $this->cambiarEstado($id_pedido);
            }
        }

        if (preg_match('/^(\d+)\/estado$/', $ruta_interna, $coincidencias)) {
            if ($metodo !== 'POST') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->cambiarEstado((int) $coincidencias[1]);
        }

        if (preg_match('/^(\d+)\/senia$/', $ruta_interna, $coincidencias)) {
            if ($metodo !== 'POST') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->actualizarSeña((int) $coincidencias[1]);
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    // ========================================================================
    // REGISTRAR PEDIDO (con senia 50% automática y delivery)
    // ========================================================================
    private function registrarPedido(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $entrada = $this->leerCuerpoJson();

        $id_mesa = isset($entrada['id_mesa']) && $entrada['id_mesa'] !== '' ? (int) $entrada['id_mesa'] : null;
        $detalles = $entrada['detalles'] ?? [];
        $tipo_pedido = $entrada['tipo_pedido'] ?? 'con_reserva';
        $senia_manual = isset($entrada['senia']) ? (float) $entrada['senia'] : -1.0; // -1 = no enviado, calcular automático
        $estado_senia = $entrada['estado_senia'] ?? 'pendiente';
        $direccion_domicilio = $entrada['direccion_domicilio'] ?? null;
        $id_zona_delivery = isset($entrada['id_zona_delivery']) && $entrada['id_zona_delivery'] !== '' ? (int) $entrada['id_zona_delivery'] : null;
        $costo_envio_manual = isset($entrada['costo_envio']) ? (float) $entrada['costo_envio'] : -1.0;
        $id_repartidor = isset($entrada['id_repartidor']) && $entrada['id_repartidor'] !== '' ? (int) $entrada['id_repartidor'] : null;
        $observaciones = $entrada['observaciones'] ?? null;

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

            $clave = $tipo_pedido === 'para_llevar' ? 'limite_pedidos_sin_reserva_retiro' : 'limite_pedidos_sin_reserva_domicilio';
            $stmt = $pdo->prepare('SELECT valor AS limite FROM configuracion_sistema WHERE clave = ?');
            $stmt->execute([$clave]);
            $config = $stmt->fetch();
            $limite = $config ? (int) $config['limite'] : 0;

            if ($limite > 0) {
                $stmt = $pdo->prepare('SELECT cantidad_pedidos FROM contador_pedidos_sin_reserva WHERE fecha = ? AND tipo_pedido = ?');
                $stmt->execute([$fecha, $tipoContador]);
                $contador = $stmt->fetch();
                $cantidadActual = $contador ? (int) $contador['cantidad_pedidos'] : 0;

                if ($cantidadActual >= $limite) {
                    return $this->error(
                        'Se ha alcanzado el límite máximo de pedidos sin reserva para ' .
                        ($tipo_pedido === 'para_llevar' ? 'retiro' : 'delivery') .
                        ' hoy. Intente con una reserva de mesa.',
                        403
                    );
                }
            }
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        // Validar productos, stock, límite de venta y calcular total
        $total_pedido = 0;
        $detallesConPrecio = [];

        foreach ($detalles as $detalle) {
            $id_producto = (int) ($detalle['id_producto'] ?? 0);
            $cantidad = (int) ($detalle['cantidad'] ?? 0);

            if ($id_producto <= 0 || $cantidad <= 0) {
                return $this->error('Datos de producto inválidos en el pedido.', 400);
            }

            // Obtener precio del producto desde BD (no confiar en precio enviado por cliente)
            $stmt = $pdo->prepare('SELECT precio FROM productos WHERE id_producto = ? AND activo = 1');
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch();

            if (!$producto) {
                return $this->error('El producto id ' . $id_producto . ' no existe o no está disponible.', 400);
            }

            $precio_unitario = (float) $producto['precio'];

            // Verificar stock
            $stmt = $pdo->prepare('SELECT stock_actual, limite_venta FROM stock_producto WHERE id_producto = ? AND activo = 1');
            $stmt->execute([$id_producto]);
            $stock = $stmt->fetch();

            if (!$stock) {
                return $this->error('El producto id ' . $id_producto . ' no tiene stock configurado.', 400);
            }

            if ((int) $stock['stock_actual'] < $cantidad) {
                return $this->error('Stock insuficiente para el producto id ' . $id_producto . '.', 400);
            }

            // Validar límite de venta
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

            $subtotal = $cantidad * $precio_unitario;
            $total_pedido += $subtotal;

            $detallesConPrecio[] = [
                'id_producto' => $id_producto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario,
                'subtotal' => $subtotal,
            ];
        }

        // Calcular senia automáticamente: 50% del total si no se envió manualmente
        if ($senia_manual < 0) {
            $senia = round($total_pedido * 0.50, 2);
        } else {
            $senia = $senia_manual;
            if ($senia < 0 || $senia > $total_pedido) {
                return $this->error('El monto de la senia debe estar entre 0 y el total del pedido.', 400);
            }
        }

        // Calcular costo de envío para delivery
        $costo_envio = 0.00;
        if ($tipo_pedido === 'domicilio' && $id_zona_delivery !== null && $id_zona_delivery > 0) {
            $stmt = $pdo->prepare('SELECT costo_envio FROM zonas_delivery WHERE id_zona_delivery = ? AND activa = 1');
            $stmt->execute([$id_zona_delivery]);
            $zona = $stmt->fetch();
            if ($zona) {
                $costo_envio = (float) $zona['costo_envio'];
            }
        } elseif ($costo_envio_manual >= 0) {
            $costo_envio = $costo_envio_manual;
        }

        $total_con_envio = $total_pedido + $costo_envio;

        // Si es domicilio y no hay senia manual, la senia es 50% del total (sin envío)
        // El saldo pendiente es total - senia
        $saldo_pendiente = round($total_con_envio - $senia, 2);

        try {
            $pdo->beginTransaction();

            // Generar número de pedido
            $numeroPedido = 'PED-' . date('Ymd') . '-' . str_pad((string) ((int) ($pdo->query('SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURRENT_DATE')->fetchColumn() + 1)), 4, '0', STR_PAD_LEFT);

            // Registrar pedido
            $stmt = $pdo->prepare('
                INSERT INTO pedidos (
                    id_mesa, id_mesero, id_usuario, fecha_pedido, estado, total,
                    tipo_pedido, senia, estado_senia, saldo_pendiente, direccion_domicilio,
                    id_zona_delivery, costo_envio, id_repartidor, numero_pedido,
                    observaciones, fecha_pedido_hora, fecha_creacion
                ) VALUES (
                    :id_mesa, :id_mesero, :id_usuario, CURRENT_TIMESTAMP, "pendiente", :total,
                    :tipo_pedido, :senia, :estado_senia, :saldo_pendiente, :direccion_domicilio,
                    :id_zona_delivery, :costo_envio, :id_repartidor, :numero_pedido,
                    :observaciones, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ');
            $stmt->execute([
                ':id_mesa' => $id_mesa,
                ':id_mesero' => $id_mesero,
                ':id_usuario' => $id_usuario,
                ':total' => $total_con_envio,
                ':tipo_pedido' => $tipo_pedido,
                ':senia' => $senia,
                ':estado_senia' => $estado_senia,
                ':saldo_pendiente' => $saldo_pendiente,
                ':direccion_domicilio' => $direccion_domicilio,
                ':id_zona_delivery' => $id_zona_delivery,
                ':costo_envio' => $costo_envio,
                ':id_repartidor' => $id_repartidor,
                ':numero_pedido' => $numeroPedido,
                ':observaciones' => $observaciones,
            ]);
            $id_pedido = (int) $pdo->lastInsertId();

            // Insertar detalles y descontar stock
            foreach ($detallesConPrecio as $detalle) {
                $stmt = $pdo->prepare('
                    INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$id_pedido, $detalle['id_producto'], $detalle['cantidad'], $detalle['precio_unitario']]);

                // Descontar stock
                $stmt = $pdo->prepare('
                    UPDATE stock_producto SET stock_actual = stock_actual - ?, fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_producto = ?
                ');
                $stmt->execute([$detalle['cantidad'], $detalle['id_producto']]);
            }

            $pdo->commit();

            // Historial
            $this->insertarHistorial($pdo, 'pedidos', $id_pedido, 'CREAR_PEDIDO', [
                'numero_pedido' => $numeroPedido,
                'id_mesa' => $id_mesa,
                'total' => $total_con_envio,
                'tipo_pedido' => $tipo_pedido,
                'senia' => $senia,
                'saldo_pendiente' => $saldo_pendiente,
            ], $id_mesero);

            // RF-69: Contabilizar pedidos sin reserva
            if ($tipo_pedido === 'para_llevar' || $tipo_pedido === 'domicilio') {
                $tipoContador = $tipo_pedido === 'para_llevar' ? 'para_llevar' : 'domicilio';
                $stmtContador = $pdo->prepare('
                    INSERT INTO contador_pedidos_sin_reserva (fecha, tipo_pedido, cantidad_pedidos)
                    VALUES (:f, :t, 1)
                    ON DUPLICATE KEY UPDATE cantidad_pedidos = cantidad_pedidos + 1, fecha_actualizacion = CURRENT_TIMESTAMP
                ');
                $stmtContador->execute([':f' => date('Y-m-d'), ':t' => $tipoContador]);
            }

            return [
                'exito' => true,
                'datos' => [
                    'id_pedido' => $id_pedido,
                    'numero_pedido' => $numeroPedido,
                    'id_mesa' => $id_mesa,
                    'estado' => 'pendiente',
                    'total' => $total_con_envio,
                    'subtotal_productos' => $total_pedido,
                    'costo_envio' => $costo_envio,
                    'senia' => $senia,
                    'estado_senia' => $estado_senia,
                    'saldo_pendiente' => $saldo_pendiente,
                    'tipo_pedido' => $tipo_pedido,
                ],
                'mensaje' => 'Pedido registrado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            $pdo->rollBack();
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return $this->error('Error al registrar el pedido: ' . $e->getMessage(), 500);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return $this->error('Error inesperado: ' . $e->getMessage(), 500);
        }
    }

    // ========================================================================
    // MIS PEDIDOS (cliente)
    // ========================================================================
    private function misPedidos(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

        $pdo = \Restaurante\Database\Conexion::obtener();
        $stmt = $pdo->prepare('
            SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.estado, p.total,
                   p.tipo_pedido, p.senia, p.estado_senia, p.saldo_pendiente,
                   p.costo_envio, p.id_zona_delivery, p.id_repartidor, p.id_mesa,
                   p.id_mesero,
                   m.numero_mesa
            FROM pedidos p
            LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
            WHERE p.id_usuario = ?
            ORDER BY p.fecha_pedido DESC
        ');
        $stmt->execute([$id_usuario]);
        $pedidos = $stmt->fetchAll();

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => 'Pedidos obtenidos correctamente.',
        ];
    }

    private function misPedidosConDetalle(): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

        $pdo = \Restaurante\Database\Conexion::obtener();
        $stmt = $pdo->prepare('
            SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.estado, p.total,
                   p.tipo_pedido, p.senia, p.estado_senia, p.saldo_pendiente,
                   p.costo_envio, p.id_zona_delivery, p.id_repartidor, p.id_mesa,
                   p.id_mesero,
                   m.numero_mesa
            FROM pedidos p
            LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
            WHERE p.id_usuario = ?
            ORDER BY p.fecha_pedido DESC
        ');
        $stmt->execute([$id_usuario]);
        $pedidos = $stmt->fetchAll();

        $resultado = [];
        foreach ($pedidos as $pedido) {
            $id_pedido = (int) $pedido['id_pedido'];

            // Obtener detalles
            $stmtDet = $pdo->prepare('
                SELECT dp.id_producto, pr.nombre AS nombre_producto, dp.cantidad, dp.precio_unitario,
                       (dp.cantidad * dp.precio_unitario) AS subtotal
                FROM detalle_pedidos dp
                JOIN productos pr ON pr.id_producto = dp.id_producto
                WHERE dp.id_pedido = ?
            ');
            $stmtDet->execute([$id_pedido]);
            $detalles = $stmtDet->fetchAll();

            $resultado[] = array_merge($this->mapearPedido($pedido), [
                'detalles' => array_map(function ($d) {
                    return [
                        'id_producto' => (int) $d['id_producto'],
                        'nombre_producto' => $d['nombre_producto'],
                        'cantidad' => (int) $d['cantidad'],
                        'precio_unitario' => (float) $d['precio_unitario'],
                        'subtotal' => (float) $d['subtotal'],
                    ];
                }, $detalles),
            ]);
        }

        return [
            'exito' => true,
            'datos' => $resultado,
            'mensaje' => 'Pedidos con detalle obtenidos correctamente.',
        ];
    }

    // ========================================================================
    // LISTAR TODOS (encargado/admin)
    // ========================================================================
    private function listarTodos(): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $pdo = \Restaurante\Database\Conexion::obtener();
        $stmt = $pdo->query('
            SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.estado, p.total,
                   p.tipo_pedido, p.senia, p.estado_senia, p.saldo_pendiente,
                   p.costo_envio, p.id_zona_delivery, p.id_repartidor, p.id_mesa,
                   p.id_mesero,
                   m.numero_mesa,
                   u.nombre AS nombre_usuario, u.email AS email_usuario
            FROM pedidos p
            LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
            LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
            ORDER BY p.fecha_pedido DESC
        ');
        $pedidos = $stmt->fetchAll();

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => 'Todos los pedidos obtenidos correctamente.',
        ];
    }

    // ========================================================================
    // POR ESTADO
    // ========================================================================
    private function porEstado(string $estado): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $validos = ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
        if (!in_array($estado, $validos, true)) {
            return $this->error('Estado no válido.', 400);
        }

        $pdo = \Restaurante\Database\Conexion::obtener();
        $stmt = $pdo->prepare('
            SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.estado, p.total,
                   p.tipo_pedido, p.senia, p.estado_senia, p.saldo_pendiente,
                   p.costo_envio, p.id_zona_delivery, p.id_repartidor, p.id_mesa,
                   p.id_mesero,
                   m.numero_mesa,
                   u.nombre AS nombre_usuario
            FROM pedidos p
            LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
            LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
            WHERE p.estado = ?
            ORDER BY p.fecha_pedido DESC
        ');
        $stmt->execute([$estado]);
        $pedidos = $stmt->fetchAll();

        return [
            'exito' => true,
            'datos' => $this->mapearPedidos($pedidos),
            'mensaje' => "Pedidos con estado '{$estado}' obtenidos correctamente.",
        ];
    }

    // ========================================================================
    // DETALLE DE PEDIDO
    // ========================================================================
    private function obtenerDetalle(int $id_pedido): array
    {
        $this->authMiddleware->requiereAutenticacion();

        $id_usuario = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();
        $esAdmin = \Restaurante\Service\ServicioDeAutenticacion::esAdministrador();
        $esEncargado = \Restaurante\Service\ServicioDeAutenticacion::esMesero();

        $pdo = \Restaurante\Database\Conexion::obtener();
        $stmt = $pdo->prepare('
            SELECT p.*, m.numero_mesa, u.nombre AS nombre_usuario, u.email AS email_usuario
            FROM pedidos p
            LEFT JOIN mesas m ON m.id_mesa = p.id_mesa
            LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
            WHERE p.id_pedido = ?
        ');
        $stmt->execute([$id_pedido]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            return $this->error('Pedido no encontrado.', 404);
        }

        // Validar propiedad o rol
        if (!($esAdmin || $esEncargado) && (int) $pedido['id_usuario'] !== $id_usuario) {
            return $this->error('No puede ver este pedido.', 403);
        }

        // Obtener detalles
        $stmtDet = $pdo->prepare('
            SELECT dp.id_producto, pr.nombre AS nombre_producto, pr.categoria,
                   dp.cantidad, dp.precio_unitario,
                   (dp.cantidad * dp.precio_unitario) AS subtotal
            FROM detalle_pedidos dp
            JOIN productos pr ON pr.id_producto = dp.id_producto
            WHERE dp.id_pedido = ?
            ORDER BY pr.nombre
        ');
        $stmtDet->execute([$id_pedido]);
        $detalles = $stmtDet->fetchAll();

        return [
            'exito' => true,
            'datos' => array_merge($this->mapearPedido($pedido), [
                'numero_mesa' => $pedido['numero_mesa'],
                'nombre_usuario' => $pedido['nombre_usuario'],
                'email_usuario' => $pedido['email_usuario'],
                'observaciones' => $pedido['observaciones'] ?? null,
                'detalles' => array_map(function ($d) {
                    return [
                        'id_producto' => (int) $d['id_producto'],
                        'nombre_producto' => $d['nombre_producto'],
                        'categoria' => $d['categoria'],
                        'cantidad' => (int) $d['cantidad'],
                        'precio_unitario' => (float) $d['precio_unitario'],
                        'subtotal' => (float) $d['subtotal'],
                    ];
                }, $detalles),
            ]),
            'mensaje' => 'Detalle del pedido obtenido.',
        ];
    }

    // ========================================================================
    // CAMBIAR ESTADO
    // ========================================================================
    private function cambiarEstado(int $id_pedido): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $entrada = $this->leerCuerpoJson();
        $estado_nuevo = $entrada['estado'] ?? '';

        if ($estado_nuevo === '') {
            return $this->error('Se requiere el parámetro estado.', 400);
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        $stmt = $pdo->prepare('SELECT id_pedido, estado FROM pedidos WHERE id_pedido = ?');
        $stmt->execute([$id_pedido]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            return $this->error('Pedido no encontrado.', 404);
        }

        $estado_actual = $pedido['estado'];

        $transicionesValidas = [
            'pendiente' => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['listo', 'cancelado'],
            'listo' => ['entregado', 'cancelado'],
            'entregado' => [],
            'cancelado' => [],
        ];

        if ($estado_nuevo === $estado_actual) {
            return $this->error('El pedido ya está en ese estado.', 400);
        }

        $permitidos = $transicionesValidas[$estado_actual] ?? [];
        if (!in_array($estado_nuevo, $permitidos, true)) {
            return $this->error(
                "No se puede pasar de '{$estado_actual}' a '{$estado_nuevo}'.",
                400
            );
        }

        try {
            $stmt = $pdo->prepare('UPDATE pedidos SET estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_pedido = ?');
            $stmt->execute([$estado_nuevo, $id_pedido]);

            // Historial
            $this->insertarHistorial($pdo, 'pedidos', $id_pedido, 'CAMBIAR_ESTADO_PEDIDO', [
                'id_pedido' => $id_pedido,
                'estado_anterior' => $estado_actual,
                'nuevo_estado' => $estado_nuevo,
            ], \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado());

            return [
                'exito' => true,
                'mensaje' => "Estado del pedido cambiado a '{$estado_nuevo}'.",
            ];
        } catch (\PDOException $e) {
            return $this->error('Error al cambiar el estado del pedido.', 500);
        }
    }

    // ========================================================================
    // ACTUALIZAR SEÑA
    // ========================================================================
    private function actualizarSeña(int $id_pedido): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        $entrada = $this->leerCuerpoJson();
        $estado = $entrada['estado'] ?? 'pagada';
        $monto = isset($entrada['monto']) ? (float) $entrada['monto'] : null;

        if (!in_array($estado, ['pendiente', 'pagada', 'devuelta'], true)) {
            return $this->error('Estado de senia inválido. Use: pendiente, pagada o devuelta.', 400);
        }

        $pdo = \Restaurante\Database\Conexion::obtener();

        $stmt = $pdo->prepare('SELECT id_pedido, senia, estado_senia, total, saldo_pendiente FROM pedidos WHERE id_pedido = ?');
        $stmt->execute([$id_pedido]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            return $this->error('Pedido no encontrado.', 404);
        }

        $seniaActual = (float) $pedido['senia'];
        $saldoActual = (float) $pedido['saldo_pendiente'];

        if ($estado === 'pagada') {
            $nuevoSaldo = max(0, round($saldoActual - $seniaActual, 2));
        } elseif ($estado === 'devuelta') {
            $nuevoSaldo = round($saldoActual + $seniaActual, 2);
        } else {
            $nuevoSaldo = $saldoActual;
        }

        if ($monto !== null && $monto >= 0) {
            $nuevoSaldo = max(0, round($saldoActual - $monto, 2));
        }

        try {
            $stmt = $pdo->prepare('
                UPDATE pedidos
                SET estado_senia = ?, saldo_pendiente = ?, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_pedido = ?
            ');
            $stmt->execute([$estado, $nuevoSaldo, $id_pedido]);

            // Historial
            $this->insertarHistorial($pdo, 'pedidos', $id_pedido, 'ACTUALIZAR_SEÑA', [
                'id_pedido' => $id_pedido,
                'estado_senia_anterior' => $pedido['estado_senia'],
                'nuevo_estado_senia' => $estado,
                'saldo_pendiente_anterior' => $saldoActual,
                'nuevo_saldo_pendiente' => $nuevoSaldo,
            ], \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado());

            return [
                'exito' => true,
                'mensaje' => 'Estado de senia actualizado.',
                'datos' => [
                    'estado_senia' => $estado,
                    'saldo_pendiente' => $nuevoSaldo,
                ],
            ];
        } catch (\PDOException $e) {
            return $this->error('Error al actualizar la senia.', 500);
        }
    }

    // ========================================================================
    // HELPERS
    // ========================================================================
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
        $cuerpo = @file_get_contents('php://input');
        if ($cuerpo === false || empty($cuerpo)) {
            $handle = @fopen('php://input', 'r');
            if ($handle) {
                $cuerpo = '';
                while (!feof($handle)) {
                    $fragmento = fread($handle, 8192);
                    if ($fragmento === false) {
                        break;
                    }
                    $cuerpo .= $fragmento;
                }
                fclose($handle);
            }
        }
        if (empty($cuerpo) && isset($_SERVER['RAW_INPUT'])) {
            $cuerpo = $_SERVER['RAW_INPUT'];
        }
        // Eliminar BOM UTF-8 si está presente
        if (substr($cuerpo, 0, 3) === "\xEF\xBB\xBF") {
            $cuerpo = substr($cuerpo, 3);
        }

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
            $resultado[] = $this->mapearPedido($pedido);
        }
        return $resultado;
    }

    private function mapearPedido(array $pedido): array
    {
        return [
            'id_pedido' => (int) $pedido['id_pedido'],
            'numero_pedido' => $pedido['numero_pedido'] ?? null,
            'id_mesa' => $pedido['id_mesa'] !== null ? (int) $pedido['id_mesa'] : null,
            'numero_mesa' => $pedido['numero_mesa'] ?? null,
            'id_mesero' => isset($pedido['id_mesero']) ? (int) $pedido['id_mesero'] : null,
            'nombre_usuario' => $pedido['nombre_usuario'] ?? null,
            'email_usuario' => $pedido['email_usuario'] ?? null,
            'id_zona_delivery' => $pedido['id_zona_delivery'] !== null ? (int) $pedido['id_zona_delivery'] : null,
            'costo_envio' => (float) ($pedido['costo_envio'] ?? 0),
            'id_repartidor' => $pedido['id_repartidor'] !== null ? (int) $pedido['id_repartidor'] : null,
            'fecha_pedido' => $pedido['fecha_pedido'] ?? null,
            'estado' => $pedido['estado'],
            'total' => (float) $pedido['total'],
            'senia' => (float) ($pedido['senia'] ?? 0),
            'estado_senia' => $pedido['estado_senia'] ?? 'pendiente',
            'saldo_pendiente' => (float) ($pedido['saldo_pendiente'] ?? 0),
            'tipo_pedido' => $pedido['tipo_pedido'],
            'fecha_actualizacion' => $pedido['fecha_actualizacion'] ?? null,
        ];
    }
}
