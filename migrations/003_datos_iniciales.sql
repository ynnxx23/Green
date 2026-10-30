-- ---------------------------------------------------------------------------
-- Migración 003: Datos iniciales de arranque
-- Roles y usuario administrador inicial.
-- Las contraseñas son valores de ejemplo para desarrollo; cambiar en producción.
-- ---------------------------------------------------------------------------

-- Roles base (3 actores: cliente, encargado, administrador)
INSERT IGNORE INTO `roles` (`id_rol`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(1, 'cliente', 'Usuario que puede ver la carta, hacer reservas y registrar pedidos.', CURRENT_TIMESTAMP),
(2, 'encargado', 'Usuario que gestiona stock, disponibilidad de mesas, pedidos y sus estados.', CURRENT_TIMESTAMP),
(4, 'administrador', 'Usuario con acceso total a gestión de usuarios, áreas, mesas, productos, reservas y pedidos.', CURRENT_TIMESTAMP);

-- Usuario administrador inicial (contraseña: admin123 - cambiar en producción)
-- Hash bcrypt generado con password_hash('admin123', PASSWORD_BCRYPT)
INSERT IGNORE INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contrasena_hash`, `id_rol`, `activo`, `fecha_creacion`) VALUES
(1, 'Administrador Sistema', 'admin@restaurante.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 4, 1, CURRENT_TIMESTAMP);
