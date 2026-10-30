-- ---------------------------------------------------------------------------
-- Migración 010: Cupones y descuentos
-- Ejecutar DESPUÉS de 001 → 009.
-- Soporta: RF-88
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cupones_descuento` (
    `id_cupon` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(50) NOT NULL,
    `tipo_descuento` ENUM('porcentaje', 'monto_fijo') NOT NULL DEFAULT 'porcentaje',
    `valor_descuento` DECIMAL(10,2) NOT NULL,
    `monto_minimo` DECIMAL(10,2) DEFAULT 0.00,
    `descuento_maximo` DECIMAL(10,2) NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NULL,
    `uso_maximo` INT UNSIGNED NULL,
    `uso_por_cliente` INT UNSIGNED DEFAULT 1,
    `veces_usado` INT UNSIGNED NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_por` INT UNSIGNED NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cupon`),
    UNIQUE KEY `uk_cupon_codigo` (`codigo`),
    KEY `idx_cupon_codigo_activo` (`codigo`, `activo`),
    KEY `idx_cupon_fechas` (`fecha_inicio`, `fecha_fin`),
    CONSTRAINT `fk_cupon_creado_por`
        FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: uso de cupones por cliente
CREATE TABLE IF NOT EXISTS `uso_cupon_cliente` (
    `id_uso` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cupon` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `fecha_uso` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `id_pedido` INT UNSIGNED NULL,
    PRIMARY KEY (`id_uso`),
    KEY `idx_uso_cupon_id_cupon` (`id_cupon`),
    KEY `idx_uso_cupon_id_usuario` (`id_usuario`),
    CONSTRAINT `fk_uso_cupon_id_cupon`
        FOREIGN KEY (`id_cupon`) REFERENCES `cupones_descuento` (`id_cupon`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_uso_cupon_id_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
