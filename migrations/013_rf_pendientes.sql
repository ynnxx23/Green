-- ============================================================================
-- MIGRACIÓN 013: Todos los RF pendientes restantes
-- Ejecutar DESPUÉS de la migración 012.
-- Soporta: RF-84, RF-85, RF-87, RF-89, RF-93, RF-94 a RF-96, RF-97 a RF-100, RF-102, RF-103, RF-105
-- ============================================================================

-- ---------------------------------------------------------------------------
-- RF-84: Estado de entrega de delivery
-- ---------------------------------------------------------------------------
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `estado_entrega` ENUM('pendiente','en_camino','entregado','fallido') NOT NULL DEFAULT 'pendiente' AFTER `id_repartidor`;
ALTER TABLE `pedidos` ADD KEY `idx_pedido_estado_entrega` (`estado_entrega`);

-- ---------------------------------------------------------------------------
-- RF-87: Cierre de caja diario
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cierre_caja` (
    `id_cierre` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fecha` DATE NOT NULL,
    `apertura_por` INT UNSIGNED NULL,
    `cierre_por` INT UNSIGNED NULL,
    `monto_apertura` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `monto_cierre` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `ventas_totales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `pedidos_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `efectivo` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tarjeta` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `transferencia` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `diferencia` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
    `fecha_apertura` DATETIME NULL,
    `fecha_cierre` DATETIME NULL,
    `observaciones` TEXT NULL,
    PRIMARY KEY (`id_cierre`),
    UNIQUE KEY `uk_cierre_fecha` (`fecha`),
    KEY `idx_cierre_estado` (`estado`),
    CONSTRAINT `fk_cierre_apertura_por` FOREIGN KEY (`apertura_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cierre_cierre_por` FOREIGN KEY (`cierre_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RF-89: Historial de cliente y platos favoritos
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `platos_favoritos` (
    `id_favorito` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `id_producto` INT UNSIGNED NOT NULL,
    `fecha_agregado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_favorito`),
    UNIQUE KEY `uk_favorito_usuario_producto` (`id_usuario`, `id_producto`),
    KEY `idx_favorito_usuario` (`id_usuario`),
    CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_favorito_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RF-93: Reservas recurrentes (tabla ya creada en 011, asegurar existencia)
-- ---------------------------------------------------------------------------
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
    CONSTRAINT `fk_recurrente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_recurrente_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RF-94 a RF-96: Notificaciones (simuladas, tabla de registro)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id_notificacion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `tipo` ENUM('reserva','pedido','pago','sistema') NOT NULL DEFAULT 'sistema',
    `canal` ENUM('whatsapp','email','push') NOT NULL,
    `asunto` VARCHAR(200) NOT NULL,
    `mensaje` TEXT NULL,
    `estado` ENUM('pendiente','enviado','fallido','leido') NOT NULL DEFAULT 'pendiente',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_envio` DATETIME NULL,
    `fecha_lectura` DATETIME NULL,
    PRIMARY KEY (`id_notificacion`),
    KEY `idx_notificacion_usuario` (`id_usuario`),
    KEY `idx_notificacion_estado` (`estado`),
    CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RF-97 a RF-100: Reportes avanzados (tablas de soporte si es necesario)
-- No requieren tablas nuevas, usan vistas SQL sobre tablas existentes.
-- Se implementan como endpoints adicionales.

-- ---------------------------------------------------------------------------
-- RF-102: Gestión de sucursales múltiples
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursales` (
    `id_sucursal` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `direccion` VARCHAR(255) NULL,
    `telefono` VARCHAR(50) NULL,
    `email` VARCHAR(100) NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_sucursal`),
    KEY `idx_sucursal_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campo id_sucursal en usuarios y pedidos
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `id_sucursal` INT UNSIGNED NULL AFTER `id_rol`;
ALTER TABLE `usuarios` ADD KEY `idx_usuario_sucursal` (`id_sucursal`);
ALTER TABLE `usuarios` ADD CONSTRAINT `fk_usuario_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `id_sucursal` INT UNSIGNED NULL AFTER `estado_entrega`;
ALTER TABLE `pedidos` ADD KEY `idx_pedido_sucursal` (`id_sucursal`);
ALTER TABLE `pedidos` ADD CONSTRAINT `fk_pedido_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `reservas` ADD COLUMN IF NOT EXISTS `id_sucursal` INT UNSIGNED NULL AFTER `observaciones`;
ALTER TABLE `reservas` ADD KEY `idx_reserva_sucursal` (`id_sucursal`);
ALTER TABLE `reservas` ADD CONSTRAINT `fk_reserva_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Datos iniciales: sucursal principal
INSERT INTO `sucursales` (`nombre`, `direccion`, `telefono`, `email`, `activa`) VALUES
    ('Green en la Casa Blanca - Principal', 'Av. Principal 123, Ciudad', '+57 300 000 0000', 'reservas@greencasablanca.com', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- ---------------------------------------------------------------------------
-- RF-103: Respaldos automáticos (registro de respaldos)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `respaldos_bd` (
    `id_respaldo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `tipo` ENUM('completo','parcial','programado','manual') NOT NULL DEFAULT 'manual',
    `tamaño_bytes` INT UNSIGNED NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `creado_por` INT UNSIGNED NULL,
    `estado` ENUM('exitoso','fallido','en_progreso') NOT NULL DEFAULT 'en_progreso',
    PRIMARY KEY (`id_respaldo`),
    KEY `idx_respaldo_fecha` (`fecha_creacion`),
    CONSTRAINT `fk_respaldo_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RF-105: Roles y permisos avanzados granulares
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permisos` (
    `id_permiso` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `codigo` VARCHAR(100) NOT NULL COMMENT 'Ej: pedidos.ver, reservas.cancelar',
    `modulo` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    PRIMARY KEY (`id_permiso`),
    UNIQUE KEY `uk_permiso_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles_permisos` (
    `id_rol` INT UNSIGNED NOT NULL,
    `id_permiso` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id_rol`, `id_permiso`),
    KEY `idx_rp_permiso` (`id_permiso`),
    CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permisos base del sistema
INSERT INTO `permisos` (`nombre`, `codigo`, `modulo`, `descripcion`) VALUES
    ('Ver pedidos', 'pedidos.ver', 'pedidos', 'Puede ver la lista de pedidos'),
    ('Crear pedidos', 'pedidos.crear', 'pedidos', 'Puede crear nuevos pedidos'),
    ('Cambiar estado de pedidos', 'pedidos.estado', 'pedidos', 'Puede cambiar el estado de los pedidos'),
    ('Cancelar pedidos', 'pedidos.cancelar', 'pedidos', 'Puede cancelar pedidos'),
    'Ver reservas', 'reservas.ver', 'reservas', 'Puede ver la lista de reservas'),
    ('Crear reservas', 'reservas.crear', 'reservas', 'Puede crear nuevas reservas'),
    ('Cambiar estado de reservas', 'reservas.estado', 'reservas', 'Puede cambiar el estado de las reservas'),
    ('Cancelar reservas', 'reservas.cancelar', 'reservas', 'Puede cancelar reservas'),
    ('Ver stock', 'stock.ver', 'stock', 'Puede ver el stock de productos'),
    ('Gestionar stock', 'stock.gestionar', 'stock', 'Puede actualizar stock y límites'),
    ('Ver menú semanal', 'menu.ver', 'menu', 'Puede ver el menú semanal'),
    ('Gestionar menú semanal', 'menu.gestionar', 'menu', 'Puede crear y publicar menús semanales'),
    ('Ver reportes', 'reportes.ver', 'reportes', 'Puede ver reportes de ventas'),
    ('Gestionar usuarios', 'usuarios.gestionar', 'usuarios', 'Puede crear, editar y desactivar usuarios'),
    ('Gestionar zonas delivery', 'delivery.gestionar', 'delivery', 'Puede gestionar zonas de delivery'),
    ('Gestionar repartidores', 'repartidores.gestionar', 'repartidores', 'Puede gestionar repartidores'),
    ('Gestionar cupones', 'cupones.gestionar', 'cupones', 'Puede gestionar cupones de descuento'),
    ('Ver configuración', 'config.ver', 'config', 'Puede ver la configuración del sistema'),
    ('Editar configuración', 'config.editar', 'config', 'Puede editar la configuración del sistema'),
    ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Asignar permisos al rol administrador (id_rol=4)
INSERT IGNORE INTO `roles_permisos` (`id_rol`, `id_permiso`)
SELECT 4, id_permiso FROM `permisos`;

-- Asignar permisos al rol encargado (id_rol=2)
INSERT IGNORE INTO `roles_permisos` (`id_rol`, `id_permiso`)
SELECT 2, id_permiso FROM `permisos` WHERE `codigo` IN (
    'pedidos.ver','pedidos.crear','pedidos.estado','pedidos.cancelar',
    'reservas.ver','reservas.crear','reservas.estado','reservas.cancelar',
    'stock.ver','stock.gestionar',
    'menu.ver','menu.gestionar',
    'reportes.ver',
    'delivery.gestionar','repartidores.gestionar','cupones.gestionar'
);

-- ============================================================================
-- FIN DE MIGRACIÓN 013
-- ============================================================================
