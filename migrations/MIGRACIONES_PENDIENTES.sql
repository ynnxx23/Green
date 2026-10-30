-- ============================================================================
-- MIGRACIONES PENDIENTES — Green en la Casa Blanca
-- Ejecutar DESPUÉS de la migración 008 (recuperar_contrasena_y_perfil.sql)
-- Orden: 009 → 010 → 011 → 012
-- Soporta: RF-68 a RF-71, RF-78 a RF-84, RF-88, RF-90 a RF-93, RF-101
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 009: Configuración del sistema y límites de pedidos sin reserva
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
    `id_config` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT NULL,
    `descripcion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizado_por` INT UNSIGNED NULL,
    PRIMARY KEY (`id_config`),
    UNIQUE KEY `uk_config_clave` (`clave`),
    KEY `idx_config_clave` (`clave`),
    CONSTRAINT `fk_config_actualizado_por`
        FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contador_pedidos_sin_reserva` (
    `id_contador` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha` DATE NOT NULL,
    `tipo_pedido` ENUM('para_llevar', 'domicilio') NOT NULL,
    `cantidad_pedidos` INT UNSIGNED NOT NULL DEFAULT 0,
    `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_contador`),
    UNIQUE KEY `uk_contador_fecha_tipo` (`fecha`, `tipo_pedido`),
    KEY `idx_contador_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) VALUES
    ('limite_pedidos_sin_reserva_retiro', '10', 'Límite máximo de pedidos sin reserva para retiro en restaurante por día'),
    ('limite_pedidos_sin_reserva_domicilio', '10', 'Límite máximo de pedidos sin reserva para delivery por día'),
    ('nombre_restaurante', 'Green en la Casa Blanca', 'Nombre comercial del restaurante'),
    ('direccion_restaurante', 'Av. Principal 123, Ciudad', 'Dirección física del restaurante'),
    ('telefono_restaurante', '+57 300 000 0000', 'Teléfono principal del restaurante'),
    ('whatsapp_restaurante', '+57 300 000 0001', 'WhatsApp del restaurante'),
    ('moneda', 'COP', 'Moneda utilizada para precios y totales'),
    ('impuesto_porcentaje', '0.00', 'Porcentaje de impuesto aplicable a las ventas'),
    ('seña_porcentaje', '50', 'Porcentaje de seña automática sobre el total del pedido'),
    ('horario_apertura', '12:00', 'Hora de apertura del restaurante'),
    ('horario_cierre', '23:00', 'Hora de cierre del restaurante'),
    ('dias_cerrado', 'Domingo', 'Días en que el restaurante permanece cerrado')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- ---------------------------------------------------------------------------
-- 010: Cupones y descuentos
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

-- ---------------------------------------------------------------------------
-- 011: Calificaciones, preferencias, fidelidad y reservas recurrentes
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `calificaciones_platos` (
    `id_calificacion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_producto` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `puntuacion` TINYINT UNSIGNED NOT NULL COMMENT '1 a 5 estrellas',
    `comentario` TEXT NULL,
    `fecha_calificacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_calificacion`),
    UNIQUE KEY `uk_calificacion_producto_usuario` (`id_producto`, `id_usuario`),
    KEY `idx_calificacion_producto` (`id_producto`),
    KEY `idx_calificacion_puntuacion` (`puntuacion`),
    CONSTRAINT `fk_calificacion_producto`
        FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_calificacion_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `preferencias_cliente` (
    `id_preferencia` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `tipo` ENUM('alergia', 'preferencia', 'restriccion') NOT NULL DEFAULT 'preferencia',
    `descripcion` VARCHAR(255) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_preferencia`),
    KEY `idx_preferencia_usuario` (`id_usuario`),
    CONSTRAINT `fk_preferencia_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reservas_recurrentes` (
    `id_reserva_recurrente` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `id_mesa` INT UNSIGNED NULL,
    `dia_semana` TINYINT UNSIGNED NOT NULL COMMENT '0=domingo, 1=lunes, ... 6=sábado',
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `cantidad_personas` INT UNSIGNED NOT NULL DEFAULT 1,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_reserva_recurrente`),
    KEY `idx_recurrente_usuario` (`id_usuario`),
    KEY `idx_recurrente_activa` (`activa`),
    CONSTRAINT `fk_recurrente_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_recurrente_mesa`
        FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `puntos_fidelidad` (
    `id_puntos` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `puntos_disponibles` INT UNSIGNED NOT NULL DEFAULT 0,
    `puntos_acumulados` INT UNSIGNED NOT NULL DEFAULT 0,
    `puntos_canjeados` INT UNSIGNED NOT NULL DEFAULT 0,
    `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_puntos`),
    UNIQUE KEY `uk_puntos_usuario` (`id_usuario`),
    CONSTRAINT `fk_puntos_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 012: Zonas de delivery, repartidores y campos en pedidos
-- ---------------------------------------------------------------------------

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

-- Campos de delivery en pedidos
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `id_zona_delivery` INT UNSIGNED NULL AFTER `direccion_domicilio`;
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `costo_envio` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `id_zona_delivery`;
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `id_repartidor` INT UNSIGNED NULL AFTER `costo_envio`;

ALTER TABLE `pedidos` ADD KEY `idx_pedido_zona_delivery` (`id_zona_delivery`);
ALTER TABLE `pedidos` ADD KEY `idx_pedido_repartidor` (`id_repartidor`);

ALTER TABLE `pedidos`
    ADD CONSTRAINT `fk_pedido_zona_delivery` FOREIGN KEY (`id_zona_delivery`) REFERENCES `zonas_delivery` (`id_zona`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_pedido_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id_repartidor`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================================
-- FIN DE MIGRACIONES
-- ============================================================================
