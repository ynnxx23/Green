-- ---------------------------------------------------------------------------
-- Migración 005: Pedidos con seña, menú semanal y tipos de entrega
-- ---------------------------------------------------------------------------

-- Agregar campos a pedidos para tipo de entrega, seña y domicilio
ALTER TABLE `pedidos`
    ADD COLUMN `tipo_pedido` ENUM('con_reserva', 'para_llevar', 'domicilio') NOT NULL DEFAULT 'con_reserva' COMMENT 'Tipo de pedido: con reserva, para llevar o domicilio',
    ADD COLUMN `seña` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto de seña pagada (50% del total)',
    ADD COLUMN `estado_seña` ENUM('pendiente', 'pagada', 'devuelta') NOT NULL DEFAULT 'pendiente' COMMENT 'Estado del pago de la seña',
    ADD COLUMN `direccion_domicilio` VARCHAR(255) NULL COMMENT 'Dirección para delivery',
    ADD COLUMN `fecha_pedido_hora` DATETIME NULL COMMENT 'Fecha y hora exacta del pedido';

-- Índice para consultas por tipo y estado de seña
ALTER TABLE `pedidos` ADD INDEX `idx_pedidos_tipo_estado` (`tipo_pedido`, `estado_seña`);

-- Tabla: menú semanal (platos publicados por semana)
CREATE TABLE IF NOT EXISTS `menu_semanal` (
    `id_menu_semanal` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `semana_inicio` DATE NOT NULL COMMENT 'Lunes de la semana',
    `semana_fin` DATE NOT NULL COMMENT 'Domingo de la semana',
    `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Semana publicada o no',
    `creado_por` INT UNSIGNED NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_menu_semanal`),
    UNIQUE KEY `uk_menu_semana` (`semana_inicio`),
    KEY `idx_menu_semanal_fechas` (`semana_inicio`, `semana_fin`),
    CONSTRAINT `fk_menu_creado_por` FOREIGN KEY (`creado_por`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: items del menú semanal (qué platos están en cada semana)
CREATE TABLE IF NOT EXISTS `menu_semanal_items` (
    `id_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_menu_semanal` INT UNSIGNED NOT NULL,
    `id_producto` INT UNSIGNED NOT NULL,
    `disponible` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Plato disponible esa semana',
    `precio_semanal` DECIMAL(10,2) NULL COMMENT 'Precio especial de la semana (opcional)',
    PRIMARY KEY (`id_item`),
    UNIQUE KEY `uk_menu_item` (`id_menu_semanal`, `id_producto`),
    KEY `idx_menu_item_producto` (`id_producto`),
    CONSTRAINT `fk_menu_item_menu` FOREIGN KEY (`id_menu_semanal`)
        REFERENCES `menu_semanal` (`id_menu_semanal`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_menu_item_producto` FOREIGN KEY (`id_producto`)
        REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo de observaciones a reservas
ALTER TABLE `reservas`
    ADD COLUMN `observaciones` VARCHAR(500) NULL COMMENT 'Observaciones del cliente sobre la reserva';
