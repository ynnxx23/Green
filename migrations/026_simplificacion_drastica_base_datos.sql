-- ============================================================================
-- Migracion 026 - Simplificacion drastica de la base de datos
-- ============================================================================
-- Objetivo: reducir el esquema al nucleo esencial del restaurante
-- (autenticacion, salas/mesas, carta/inventario, reservas, pedidos, auditoria)
-- y eliminar funcionalidades sin interfaz ni uso real.
--
-- Se pasan de 23 tablas a 14 tablas.
--
-- ELIMINADAS (9 tablas):
--   horarios_funcionamiento   - horario fijo del restaurante (va a configuracion por defecto)
--   bloques_mesa              - bloqueo de mesas sin uso real en interfaz
--   disponibilidad_mesa       - cupos de reserva (se verifica mesas libres directamente)
--   menu_semanal              - menu semanal es funcionalidad extra
--   menu_semanal_items        - items del menu semanal (extra)
--   zonas_delivery            - zonas de envio (delivery se quita)
--   repartidores              - personal de reparto (delivery se quita)
--   contador_pedidos_sin_reserva - limite de pedidos sin reserva (extra)
--   configuracion_sistema     - parametros pasan a valores por defecto en codigo
--
-- COLUMNAS ELIMINADAS de pedidos (delivery desaparece):
--   id_zona_delivery, id_repartidor, estado_entrega, costo_envio,
--   direccion_domicilio, fecha_pedido_hora, observaciones
-- tipo_pedido queda como ENUM('con_reserva','para_llevar').

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1. Eliminar tablas de funcionalidades no esenciales
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS menu_semanal_items;
DROP TABLE IF EXISTS menu_semanal;
DROP TABLE IF EXISTS disponibilidad_mesa;
DROP TABLE IF EXISTS bloques_mesa;
DROP TABLE IF EXISTS horarios_funcionamiento;
DROP TABLE IF EXISTS zonas_delivery;
DROP TABLE IF EXISTS repartidores;
DROP TABLE IF EXISTS contador_pedidos_sin_reserva;
DROP TABLE IF EXISTS configuracion_sistema;

-- ---------------------------------------------------------------------------
-- 2. Simplificar tabla pedidos (quitar columnas de delivery eliminado)
-- ---------------------------------------------------------------------------
ALTER TABLE pedidos
    DROP FOREIGN KEY IF EXISTS fk_pedido_zona_delivery,
    DROP FOREIGN KEY IF EXISTS fk_pedido_repartidor,
    DROP COLUMN IF EXISTS id_zona_delivery,
    DROP COLUMN IF EXISTS id_repartidor,
    DROP COLUMN IF EXISTS estado_entrega,
    DROP COLUMN IF EXISTS costo_envio,
    DROP COLUMN IF EXISTS direccion_domicilio,
    DROP COLUMN IF EXISTS fecha_pedido_hora,
    DROP COLUMN IF EXISTS observaciones;

-- tipo_pedido ya no incluye 'domicilio'
ALTER TABLE pedidos
    MODIFY COLUMN tipo_pedido ENUM('con_reserva','para_llevar') NOT NULL DEFAULT 'con_reserva';

SET FOREIGN_KEY_CHECKS = 1;
