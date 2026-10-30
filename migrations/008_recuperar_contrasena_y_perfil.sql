-- ---------------------------------------------------------------------------
-- Migración 008: Recuperación de contraseña y campos de perfil
-- Ejecutar DESPUÉS de 001 → 007.
-- ---------------------------------------------------------------------------

-- Tabla: tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS `recuperar_contrasena` (
    `id_recuperacion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` INT UNSIGNED NOT NULL,
    `token` VARCHAR(100) NOT NULL,
    `fecha_expiracion` DATETIME NOT NULL,
    `usado` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `direccion_ip` VARCHAR(45) NULL,
    PRIMARY KEY (`id_recuperacion`),
    UNIQUE KEY `uk_recuperar_token` (`token`),
    KEY `idx_recuperar_id_usuario` (`id_usuario`),
    KEY `idx_recuperar_token_expiracion` (`token`, `fecha_expiracion`),
    CONSTRAINT `fk_recuperar_id_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
