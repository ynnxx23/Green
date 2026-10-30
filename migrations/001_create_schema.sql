-- ---------------------------------------------------------------------------
-- Migración 001: Esquema inicial del Sistema de gestión de un restaurante
-- Aplicar una sola vez por entorno.
-- Compatible con MariaDB 10.11 y MySQL 8.0.
-- ---------------------------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-03:00";

-- -----------------------------------------------------
-- Tabla: roles
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
    `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(200) NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_rol`),
    UNIQUE KEY `uk_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: usuarios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `contrasena_hash` VARCHAR(255) NOT NULL,
    `id_rol` INT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`),
    UNIQUE KEY `uk_usuarios_email` (`email`),
    KEY `idx_usuarios_id_rol` (`id_rol`),
    CONSTRAINT `fk_usuarios_id_rol` FOREIGN KEY (`id_rol`)
        REFERENCES `roles` (`id_rol`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: areas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `areas` (
    `id_area` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: mesas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mesas` (
    `id_mesa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_area` INT UNSIGNED NOT NULL,
    `numero_mesa` VARCHAR(20) NOT NULL,
    `capacidad` INT NOT NULL DEFAULT 1,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_mesa`),
    UNIQUE KEY `uk_mesas_area_numero` (`id_area`, `numero_mesa`),
    KEY `idx_mesas_id_area` (`id_area`),
    CONSTRAINT `fk_mesas_id_area` FOREIGN KEY (`id_area`)
        REFERENCES `areas` (`id_area`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: horarios_funcionamiento
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `horarios_funcionamiento` (
    `id_horario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_area` INT UNSIGNED NOT NULL,
    `dia_semana` TINYINT UNSIGNED NOT NULL COMMENT '0=domingo, 1=lunes, ..., 6=sábado',
    `hora_apertura` TIME NOT NULL,
    `hora_cierre` TIME NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_horario`),
    UNIQUE KEY `uk_horarios_area_dia` (`id_area`, `dia_semana`),
    KEY `idx_horarios_id_area` (`id_area`),
    CONSTRAINT `chk_horario_cierre_mayor_apertura` CHECK (`hora_cierre` > `hora_apertura`),
    CONSTRAINT `fk_horarios_id_area` FOREIGN KEY (`id_area`)
        REFERENCES `areas` (`id_area`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: bloques_mesa
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bloques_mesa` (
    `id_bloque` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_mesa` INT UNSIGNED NOT NULL,
    `fecha_inicio` DATETIME NOT NULL,
    `fecha_fin` DATETIME NOT NULL,
    `motivo` VARCHAR(200) NULL,
    `creado_por` INT UNSIGNED NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_bloque`),
    KEY `idx_bloques_id_mesa` (`id_mesa`),
    KEY `idx_bloques_fechas` (`fecha_inicio`, `fecha_fin`),
    KEY `idx_bloques_creado_por` (`creado_por`),
    CONSTRAINT `chk_bloque_fin_mayor_inicio` CHECK (`fecha_fin` > `fecha_inicio`),
    CONSTRAINT `fk_bloques_id_mesa` FOREIGN KEY (`id_mesa`)
        REFERENCES `mesas` (`id_mesa`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_bloques_creado_por` FOREIGN KEY (`creado_por`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: reservas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservas` (
    `id_reserva` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_mesa` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `fecha_reserva` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_reserva`),
    KEY `idx_reservas_id_mesa_fecha` (`id_mesa`, `fecha_reserva`, `hora_inicio`, `hora_fin`),
    KEY `idx_reservas_id_usuario` (`id_usuario`),
    KEY `idx_reservas_estado` (`estado`),
    CONSTRAINT `chk_reserva_fin_mayor_inicio` CHECK (`hora_fin` > `hora_inicio`),
    CONSTRAINT `chk_reserva_estado_valido` CHECK (`estado` IN ('pendiente', 'confirmada', 'cancelada', 'vencida', 'completada')),
    CONSTRAINT `fk_reservas_id_mesa` FOREIGN KEY (`id_mesa`)
        REFERENCES `mesas` (`id_mesa`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reservas_id_usuario` FOREIGN KEY (`id_usuario`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: productos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `productos` (
    `id_producto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `categoria` VARCHAR(50) NULL,
    `precio` DECIMAL(10,2) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_producto`),
    CONSTRAINT `chk_producto_precio_no_negativo` CHECK (`precio` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: pedidos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pedidos` (
    `id_pedido` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_mesa` INT UNSIGNED NOT NULL,
    `id_mesero` INT UNSIGNED NOT NULL,
    `fecha_pedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pedido`),
    KEY `idx_pedidos_id_mesa` (`id_mesa`),
    KEY `idx_pedidos_id_mesero` (`id_mesero`),
    KEY `idx_pedidos_estado` (`estado`),
    CONSTRAINT `chk_pedido_total_no_negativo` CHECK (`total` >= 0),
    CONSTRAINT `chk_pedido_estado_valido` CHECK (`estado` IN ('pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado')),
    CONSTRAINT `fk_pedidos_id_mesa` FOREIGN KEY (`id_mesa`)
        REFERENCES `mesas` (`id_mesa`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pedidos_id_mesero` FOREIGN KEY (`id_mesero`)
        REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: detalle_pedidos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `detalle_pedidos` (
    `id_detalle` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_pedido` INT UNSIGNED NOT NULL,
    `id_producto` INT UNSIGNED NOT NULL,
    `cantidad` INT NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id_detalle`),
    KEY `idx_detalle_pedidos_id_pedido` (`id_pedido`),
    KEY `idx_detalle_pedidos_id_producto` (`id_producto`),
    CONSTRAINT `chk_detalle_cantidad_positiva` CHECK (`cantidad` >= 1),
    CONSTRAINT `chk_detalle_precio_no_negativo` CHECK (`precio_unitario` >= 0),
    CONSTRAINT `fk_detalle_pedidos_id_pedido` FOREIGN KEY (`id_pedido`)
        REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detalle_pedidos_id_producto` FOREIGN KEY (`id_producto`)
        REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: intentos_login
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `intentos_login` (
    `id_intento` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(150) NOT NULL,
    `exitoso` TINYINT(1) NOT NULL,
    `fecha_intento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `direccion_ip` VARCHAR(45) NULL,
    PRIMARY KEY (`id_intento`),
    KEY `idx_intentos_email_fecha` (`email`, `fecha_intento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: rate_limit_state (estado de Token Bucket por cliente/endpoint)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_limit_state` (
    `clave` VARCHAR(128) NOT NULL,
    `tokens` DECIMAL(10,4) NOT NULL DEFAULT 0,
    `ultima_actualizacion` DATETIME NOT NULL,
    PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: migraciones_aplicadas (control de versionado)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `migraciones_aplicadas` (
    `version` VARCHAR(50) NOT NULL,
    `fecha_aplicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
