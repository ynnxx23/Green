-- ---------------------------------------------------------------------------
-- Migración 004: Stock / límite de ventas por plato y disponibilidad多fecha
-- ---------------------------------------------------------------------------

-- Stock y límite de venta por producto (para que el encargado controle cuántos
-- pedidos se pueden tomar de cada plato)
CREATE TABLE IF NOT EXISTS `stock_producto` (
    `id_stock` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_producto` INT UNSIGNED NOT NULL,
    `stock_actual` INT NOT NULL DEFAULT 0,
    `limite_venta` INT NOT NULL DEFAULT 0 COMMENT '0 = sin límite',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_stock`),
    UNIQUE KEY `uk_stock_producto` (`id_producto`),
    KEY `idx_stock_id_producto` (`id_producto`),
    CONSTRAINT `fk_stock_id_producto` FOREIGN KEY (`id_producto`)
        REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Disponibilidad de mesas por fecha y rango de horas (encargado abre disponibilidad)
CREATE TABLE IF NOT EXISTS `disponibilidad_mesa` (
    `id_disponibilidad` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `mesas_disponibles` INT NOT NULL DEFAULT 1,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_por` INT UNSIGNED NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_disponibilidad`),
    UNIQUE KEY `uk_disponibilidad_fecha_hora` (`fecha`, `hora_inicio`),
    KEY `idx_disponibilidad_fecha` (`fecha`),
    CONSTRAINT `chk_disp_fin_mayor_inicio` CHECK (`hora_fin` > `hora_inicio`),
    CONSTRAINT `fk_disp_creado_por` FOREIGN KEY (`creado_por`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
