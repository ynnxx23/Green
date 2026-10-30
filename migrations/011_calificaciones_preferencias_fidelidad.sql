-- ---------------------------------------------------------------------------
-- Migración 011: Calificaciones, preferencias y reservas recurrentes
-- Ejecutar DESPUÉS de 001 → 010.
-- Soporta: RF-91, RF-92, RF-93
-- ---------------------------------------------------------------------------

-- Tabla: calificaciones y reseñas de platos (RF-91)
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

-- Tabla: preferencias y alergias del cliente (RF-92)
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

-- Tabla: reservas recurrentes (RF-93)
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

-- Tabla: puntos de fidelidad del cliente (RF-90)
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
