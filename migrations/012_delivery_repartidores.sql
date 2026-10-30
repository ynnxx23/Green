-- ---------------------------------------------------------------------------
-- Migración 012: Zonas de delivery y repartidores
-- Ejecutar DESPUÉS de 001 → 011.
-- Soporta: RF-81, RF-83
-- ---------------------------------------------------------------------------

-- Tabla: zonas de delivery (RF-81)
CREATE TABLE IF NOT EXISTS `zonas_delivery` (
    `id_zona` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `costo_envio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tiempo_estimado_minutos` INT UNSIGNED NOT NULL DEFAULT 45,
    `cobertura` TEXT NULL COMMENT 'Barrios, direcciones o rangos cubiertos',
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_zona`),
    KEY `idx_zona_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: repartidores (RF-83)
CREATE TABLE IF NOT EXISTS `repartidores` (
    `id_repartidor` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NULL COMMENT 'Usuario del sistema si tiene cuenta',
    `nombre` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(50) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_repartidor`),
    UNIQUE KEY `uk_repartidor_usuario` (`id_usuario`),
    KEY `idx_repartidor_activo` (`activo`),
    CONSTRAINT `fk_repartidor_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campo id_zona en pedidos para delivery (RF-81)
ALTER TABLE `pedidos` ADD COLUMN `id_zona_delivery` INT UNSIGNED NULL AFTER `direccion_domicilio`;
ALTER TABLE `pedidos` ADD COLUMN `costo_envio` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `id_zona_delivery`;
ALTER TABLE `pedidos` ADD COLUMN `id_repartidor` INT UNSIGNED NULL AFTER `costo_envio`;

-- Índices para las nuevas columnas de pedidos
ALTER TABLE `pedidos` ADD KEY `idx_pedido_zona_delivery` (`id_zona_delivery`);
ALTER TABLE `pedidos` ADD KEY `idx_pedido_repartidor` (`id_repartidor`);

-- Claves foráneas para las nuevas columnas de pedidos
ALTER TABLE `pedidos`
    ADD CONSTRAINT `fk_pedido_zona_delivery` FOREIGN KEY (`id_zona_delivery`) REFERENCES `zonas_delivery` (`id_zona`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_pedido_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id_repartidor`) ON DELETE SET NULL ON UPDATE CASCADE;
