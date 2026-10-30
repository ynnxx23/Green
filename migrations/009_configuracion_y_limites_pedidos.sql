-- ---------------------------------------------------------------------------
-- Migración 009: Configuración del sistema y límites de pedidos sin reserva
-- Ejecutar DESPUÉS de 001 → 008.
-- Soporta: RF-68, RF-69, RF-70, RF-71, RF-101
-- ---------------------------------------------------------------------------

-- Tabla: configuración general del restaurante (RF-101)
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

-- Tabla: contadores de pedidos sin reserva por período (RF-68, RF-69, RF-70, RF-71)
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

-- Datos iniciales de configuración (RF-101)
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
