-- ---------------------------------------------------------------------------
-- Migración 007: Datos de prueba (seed)
-- Áreas, mesas, productos, stock, disponibilidad, horarios y menú semanal.
-- Ejecutar UNA sola vez, después de 001 → 006, sobre una base de datos vacía.
-- ---------------------------------------------------------------------------
-- Usuarios de prueba (contraseña de prueba: admin123 — cambiar en producción).
-- El rol administrador (id 1) ya existe en la migración 003.
-- Nota: el registro público (registro.html) permite crear clientes y
-- encargados adicionales; estos correos ya están ocupados por los de prueba.
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contrasena_hash`, `id_rol`, `activo`, `fecha_creacion`) VALUES
(2, 'María Gómez', 'maria.cliente@test.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 1, 1, CURRENT_TIMESTAMP),
(3, 'Carlos Encargado', 'carlos.encargado@test.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 2, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Áreas del restaurante
-- -----------------------------------------------------
INSERT IGNORE INTO `areas` (`id_area`, `nombre`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Comedor Principal', 'Salón principal con vista al jardín.', 1, CURRENT_TIMESTAMP),
(2, 'Terraza', 'Espacio al aire libre, ideal para almuerzos y cenas.', 1, CURRENT_TIMESTAMP),
(3, 'Salón Privado', 'Salón privado para eventos y celebraciones.', 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Mesas (numero_mesa es único por área)
-- -----------------------------------------------------
INSERT IGNORE INTO `mesas` (`id_mesa`, `id_area`, `numero_mesa`, `capacidad`, `activo`, `fecha_creacion`) VALUES
-- Comedor Principal
(1, 1, '1', 2, 1, CURRENT_TIMESTAMP),
(2, 1, '2', 4, 1, CURRENT_TIMESTAMP),
(3, 1, '3', 4, 1, CURRENT_TIMESTAMP),
(4, 1, '4', 6, 1, CURRENT_TIMESTAMP),
(5, 1, '5', 2, 1, CURRENT_TIMESTAMP),
-- Terraza
(6, 2, '6', 2, 1, CURRENT_TIMESTAMP),
(7, 2, '7', 4, 1, CURRENT_TIMESTAMP),
(8, 2, '8', 4, 1, CURRENT_TIMESTAMP),
-- Salón Privado
(9, 3, '9', 8, 1, CURRENT_TIMESTAMP),
(10, 3, '10', 10, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Horarios de funcionamiento por área y día de la semana
-- (0=domingo, 1=lunes, ..., 6=sábado)
-- -----------------------------------------------------
INSERT IGNORE INTO `horarios_funcionamiento` (`id_horario`, `id_area`, `dia_semana`, `hora_apertura`, `hora_cierre`, `activo`, `fecha_creacion`) VALUES
(1, 1, 0, '12:00:00', '22:00:00', 1, CURRENT_TIMESTAMP),
(2, 1, 1, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(3, 1, 2, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(4, 1, 3, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(5, 1, 4, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(6, 1, 5, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP),
(7, 1, 6, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP),
(8, 2, 0, '12:00:00', '22:00:00', 1, CURRENT_TIMESTAMP),
(9, 2, 1, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(10, 2, 2, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(11, 2, 3, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(12, 2, 4, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(13, 2, 5, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP),
(14, 2, 6, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP),
(15, 3, 0, '12:00:00', '22:00:00', 1, CURRENT_TIMESTAMP),
(16, 3, 1, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(17, 3, 2, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(18, 3, 3, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(19, 3, 4, '12:00:00', '23:00:00', 1, CURRENT_TIMESTAMP),
(20, 3, 5, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP),
(21, 3, 6, '12:00:00', '23:59:00', 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Productos del menú (categoría usada por los filtros de la carta)
-- -----------------------------------------------------
INSERT IGNORE INTO `productos` (`id_producto`, `nombre`, `categoria`, `precio`, `activo`, `fecha_creacion`) VALUES
-- Entradas
(1, 'Bruschetta de tomate y albahaca', 'Entradas', 4.50, 1, CURRENT_TIMESTAMP),
(2, 'Carpaccio de res', 'Entradas', 8.00, 1, CURRENT_TIMESTAMP),
-- Principales
(3, 'Lomo saltado', 'Principales', 14.00, 1, CURRENT_TIMESTAMP),
(4, 'Arroz con mariscos', 'Principales', 16.00, 1, CURRENT_TIMESTAMP),
(5, 'Lasaña vegetal', 'Principales', 12.00, 1, CURRENT_TIMESTAMP),
(6, 'Milanesa napolitana', 'Principales', 11.00, 1, CURRENT_TIMESTAMP),
-- Postres
(7, 'Tiramisú', 'Postres', 5.00, 1, CURRENT_TIMESTAMP),
(8, 'Flan de café', 'Postres', 4.00, 1, CURRENT_TIMESTAMP),
-- Bebidas
(9, 'Jugo natural de naranja', 'Bebidas', 3.00, 1, CURRENT_TIMESTAMP),
(10, 'Agua mineral', 'Bebidas', 2.00, 1, CURRENT_TIMESTAMP),
(11, 'Vino tinto (copa)', 'Bebidas', 6.00, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Stock y límite de venta por producto (requerido para crear pedidos)
-- stock_actual = unidades disponibles; limite_venta = 0 significa sin límite
-- -----------------------------------------------------
INSERT IGNORE INTO `stock_producto` (`id_producto`, `stock_actual`, `limite_venta`, `activo`, `fecha_actualizacion`) VALUES
(1, 30, 20, 1, CURRENT_TIMESTAMP),
(2, 15, 10, 1, CURRENT_TIMESTAMP),
(3, 25, 15, 1, CURRENT_TIMESTAMP),
(4, 20, 12, 1, CURRENT_TIMESTAMP),
(5, 18, 10, 1, CURRENT_TIMESTAMP),
(6, 22, 14, 1, CURRENT_TIMESTAMP),
(7, 25, 15, 1, CURRENT_TIMESTAMP),
(8, 20, 12, 1, CURRENT_TIMESTAMP),
(9, 40, 30, 1, CURRENT_TIMESTAMP),
(10, 50, 40, 1, CURRENT_TIMESTAMP),
(11, 24, 15, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Disponibilidad de mesas para reservas (días verdes en el calendario)
-- Rango 12:00-16:00 (almuerzo) y 19:00-23:00 (cena), del 02/11 al 08/11/2026.
-- -----------------------------------------------------
INSERT IGNORE INTO `disponibilidad_mesa` (`fecha`, `hora_inicio`, `hora_fin`, `mesas_disponibles`, `activo`, `creado_por`, `fecha_creacion`) VALUES
('2026-11-02', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-02', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-03', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-03', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-04', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-04', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-05', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-05', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-06', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-06', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-07', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-07', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-08', '12:00:00', '16:00:00', 6, 1, 1, CURRENT_TIMESTAMP),
('2026-11-08', '19:00:00', '23:00:00', 6, 1, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Menú semanal vigente (semana del 02/11 al 08/11/2026)
-- -----------------------------------------------------
INSERT IGNORE INTO `menu_semanal` (`id_menu_semanal`, `semana_inicio`, `semana_fin`, `activo`, `creado_por`, `fecha_creacion`) VALUES
(1, '2026-11-02', '2026-11-08', 1, 1, CURRENT_TIMESTAMP);

INSERT IGNORE INTO `menu_semanal_items` (`id_menu_semanal`, `id_producto`, `disponible`, `precio_semanal`, `fecha_creacion`) VALUES
(1, 3, 1, 13.00, CURRENT_TIMESTAMP),  -- Lomo saltado
(1, 4, 1, 15.00, CURRENT_TIMESTAMP),  -- Arroz con mariscos
(1, 5, 1, 11.00, CURRENT_TIMESTAMP),  -- Lasaña vegetal
(1, 7, 1, 4.50, CURRENT_TIMESTAMP),   -- Tiramisú
(1, 9, 1, 2.50, CURRENT_TIMESTAMP);   -- Jugo natural de naranja

-- ---------------------------------------------------------------------------
-- Credenciales de prueba (cambiar en producción):
-- Administrador: admin@restaurante.local / admin123  (id 1, migración 003)
-- Cliente:       maria.cliente@test.local / admin123   (id 2)
-- Encargado:     carlos.encargado@test.local / admin123 (id 3)
-- ---------------------------------------------------------------------------
