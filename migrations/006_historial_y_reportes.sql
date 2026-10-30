-- ---------------------------------------------------------------------------
-- Migración 006: Normalización, historial de movimientos y reportes
-- ---------------------------------------------------------------------------

-- 1. NORMALIZACIÓN: pedidos.id_mesa debe ser nullable (para llevar/domicilio no tiene mesa)
ALTER TABLE `pedidos`
    MODIFY COLUMN `id_mesa` INT UNSIGNED NULL,
    MODIFY COLUMN `id_mesero` INT UNSIGNED NULL,
    DROP FOREIGN KEY `fk_pedidos_id_mesa`,
    DROP FOREIGN KEY `fk_pedidos_id_mesero`,
    ADD CONSTRAINT `fk_pedidos_id_mesa`
        FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_pedidos_id_mesero`
        FOREIGN KEY (`id_mesero`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar id_usuario a pedidos (quien realizó el pedido, puede ser cliente)
ALTER TABLE `pedidos`
    ADD COLUMN `id_usuario` INT UNSIGNED NULL AFTER `id_mesero`,
    ADD CONSTRAINT `fk_pedidos_id_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD KEY `idx_pedidos_id_usuario` (`id_usuario`);

-- 2. TABLA: historial_movimientos (auditoría de todo lo que ocurre en el sistema)
CREATE TABLE IF NOT EXISTS `historial_movimientos` (
    `id_historial` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tabla_afectada` VARCHAR(50) NOT NULL COMMENT 'Nombre de la tabla afectada',
    `id_registro` INT UNSIGNED NULL COMMENT 'ID del registro afectado',
    `accion` VARCHAR(20) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, RESERVA, PEDIDO, etc.',
    `detalles` JSON NULL COMMENT 'Detalles del movimiento en JSON',
    `id_usuario` INT UNSIGNED NULL COMMENT 'Usuario que realizó la acción',
    `fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `direccion_ip` VARCHAR(45) NULL,
    PRIMARY KEY (`id_historial`),
    KEY `idx_historial_tabla_fecha` (`tabla_afectada`, `fecha_movimiento`),
    KEY `idx_historial_usuario` (`id_usuario`),
    KEY `idx_historial_accion` (`accion`),
    CONSTRAINT `fk_historial_id_usuario` FOREIGN KEY (`id_usuario`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Índices adicionales para reportes rápidos
ALTER TABLE `pedidos` ADD KEY `idx_pedidos_fecha_total` (`fecha_pedido`, `total`);
ALTER TABLE `reservas` ADD KEY `idx_reservas_fecha_estado` (`fecha_reserva`, `estado`);
ALTER TABLE `detalle_pedidos` ADD KEY `idx_detalle_producto_fecha` (`id_producto`, `id_pedido`);
