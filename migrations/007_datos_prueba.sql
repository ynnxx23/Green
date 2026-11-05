-- ---------------------------------------------------------------------------
-- Migración 007: Datos de prueba (seed) - SISTEMA COMPLETO
-- Áreas, mesas, productos, stock, disponibilidad, horarios, menú semanal,
-- usuarios de prueba, y muchos pedidos de prueba para que la carta
-- destacada muestre productos más vendidos del mes.
-- Ejecutar UNA sola vez, después de 001 → 006, sobre una base de datos vacía.
-- ---------------------------------------------------------------------------
-- Usuarios de prueba (contraseña de prueba: admin123 — cambiar en producción).
-- El rol administrador (id 1) ya existe en la migración 003.
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contrasena_hash`, `id_rol`, `activo`, `fecha_creacion`) VALUES
(2, 'María Gómez', 'maria.cliente@test.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 1, 1, CURRENT_TIMESTAMP),
(3, 'Carlos Encargado', 'carlos.encargado@test.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 2, 1, CURRENT_TIMESTAMP),
(4, 'Laura Admin', 'laura.admin@test.local', '$2y$10$LaCjoOyDyXD0ZAW7PjlO3.lrtzStYCbRK4TuH96m6aW.G394jKO16', 4, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Áreas del restaurante
-- -----------------------------------------------------
INSERT IGNORE INTO `areas` (`id_area`, `nombre`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Comedor Principal', 'Salón principal con vista al jardín.', 1, CURRENT_TIMESTAMP),
(2, 'Terraza', 'Espacio al aire libre, ideal para almuerzos y cenas.', 1, CURRENT_TIMESTAMP),
(3, 'Salón Privado', 'Salón privado para eventos y celebraciones.', 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Mesas
-- -----------------------------------------------------
INSERT IGNORE INTO `mesas` (`id_mesa`, `id_area`, `numero_mesa`, `capacidad`, `activo`, `fecha_creacion`) VALUES
(1, 1, '1', 2, 1, CURRENT_TIMESTAMP),
(2, 1, '2', 4, 1, CURRENT_TIMESTAMP),
(3, 1, '3', 4, 1, CURRENT_TIMESTAMP),
(4, 1, '4', 6, 1, CURRENT_TIMESTAMP),
(5, 1, '5', 2, 1, CURRENT_TIMESTAMP),
(6, 2, '6', 2, 1, CURRENT_TIMESTAMP),
(7, 2, '7', 4, 1, CURRENT_TIMESTAMP),
(8, 2, '8', 4, 1, CURRENT_TIMESTAMP),
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
-- Productos del menú (con imágenes de la carpeta /public/assets/img/platos/)
-- Entradas
-- -----------------------------------------------------
INSERT IGNORE INTO `productos` (`id_producto`, `nombre`, `categoria`, `precio`, `imagen_url`, `activo`, `fecha_creacion`) VALUES
(1, 'Bruschetta de tomate y albahaca', 'Entradas', 4.50, '/public/assets/img/platos/pan.jpeg', 1, CURRENT_TIMESTAMP),
(2, 'Carpaccio de res', 'Entradas', 8.00, '/public/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(3, 'Croquetas de jamón', 'Entradas', 6.50, '/public/assets/img/platos/sanguche_medialunas.jpeg', 1, CURRENT_TIMESTAMP),
(4, 'Calamares a la romana', 'Entradas', 9.50, '/public/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(5, 'Nachos con queso y guacamole', 'Entradas', 7.00, '/public/assets/img/platos/sanguche_medialunas.jpeg', 1, CURRENT_TIMESTAMP),
(6, 'Alitas al estilo bufalo', 'Entradas', 8.50, '/public/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
-- Principales
(7, 'Lomo saltado', 'Principales', 14.00, '/public/assets/img/platos/lomito_papas.jpeg', 1, CURRENT_TIMESTAMP),
(8, 'Arroz con mariscos', 'Principales', 16.00, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(9, 'Lasaña vegetal', 'Principales', 12.00, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(10, 'Milanesa napolitana', 'Principales', 11.00, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(11, 'Salmón a la plancha con verduras', 'Principales', 18.00, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(12, 'Pasta a la bolognesa', 'Principales', 10.50, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(13, 'Risotto de setas', 'Principales', 13.00, '/public/assets/img/platos/pan.jpeg', 1, CURRENT_TIMESTAMP),
(14, 'Chuletas de cerdo con puré', 'Principales', 15.00, '/public/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(15, 'Pollo al curry con arroz basmati', 'Principales', 13.50, '/public/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
-- Postres
(16, 'Tiramisú', 'Postres', 5.00, '/public/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(17, 'Flan de café', 'Postres', 4.00, '/public/assets/img/platos/cafe.jpeg', 1, CURRENT_TIMESTAMP),
(18, 'Cheesecake de frutos rojos', 'Postres', 6.00, '/public/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(19, 'Helado artesanal', 'Postres', 4.50, '/public/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(20, 'Brownie de chocolate', 'Postres', 5.50, '/public/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(21, 'Mousse de chocolate', 'Postres', 5.00, '/public/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
-- Bebidas / Coctelería
(22, 'Mojito clásico', 'Bebidas', 7.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(23, 'Piña colada', 'Bebidas', 8.00, '/public/assets/img/platos/jugos.jpeg', 1, CURRENT_TIMESTAMP),
(24, 'Jugo natural de naranja', 'Bebidas', 3.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(25, 'Agua mineral', 'Bebidas', 2.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(26, 'Vino tinto (copa)', 'Bebidas', 6.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(27, 'Cerveza artesanal', 'Bebidas', 5.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(28, 'Batido de fresa', 'Bebidas', 5.50, '/public/assets/img/platos/jugos.jpeg', 1, CURRENT_TIMESTAMP),
(29, 'Té helado de durazno', 'Bebidas', 4.00, '/public/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(30, 'Café expreso', 'Bebidas', 3.50, '/public/assets/img/platos/cafe.jpeg', 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Stock y límite de venta por producto
-- -----------------------------------------------------
INSERT IGNORE INTO `stock_producto` (`id_producto`, `stock_actual`, `limite_venta`, `activo`, `fecha_actualizacion`) VALUES
(1, 30, 20, 1, CURRENT_TIMESTAMP),
(2, 15, 10, 1, CURRENT_TIMESTAMP),
(3, 20, 15, 1, CURRENT_TIMESTAMP),
(4, 18, 12, 1, CURRENT_TIMESTAMP),
(5, 25, 18, 1, CURRENT_TIMESTAMP),
(6, 22, 14, 1, CURRENT_TIMESTAMP),
(7, 25, 15, 1, CURRENT_TIMESTAMP),
(8, 20, 12, 1, CURRENT_TIMESTAMP),
(9, 18, 10, 1, CURRENT_TIMESTAMP),
(10, 22, 14, 1, CURRENT_TIMESTAMP),
(11, 15, 10, 1, CURRENT_TIMESTAMP),
(12, 20, 15, 1, CURRENT_TIMESTAMP),
(13, 15, 10, 1, CURRENT_TIMESTAMP),
(14, 18, 12, 1, CURRENT_TIMESTAMP),
(15, 16, 10, 1, CURRENT_TIMESTAMP),
(16, 25, 15, 1, CURRENT_TIMESTAMP),
(17, 20, 12, 1, CURRENT_TIMESTAMP),
(18, 18, 10, 1, CURRENT_TIMESTAMP),
(19, 30, 20, 1, CURRENT_TIMESTAMP),
(20, 15, 10, 1, CURRENT_TIMESTAMP),
(21, 20, 12, 1, CURRENT_TIMESTAMP),
(22, 30, 25, 1, CURRENT_TIMESTAMP),
(23, 15, 10, 1, CURRENT_TIMESTAMP),
(24, 40, 30, 1, CURRENT_TIMESTAMP),
(25, 50, 40, 1, CURRENT_TIMESTAMP),
(26, 24, 15, 1, CURRENT_TIMESTAMP),
(27, 35, 25, 1, CURRENT_TIMESTAMP),
(28, 20, 15, 1, CURRENT_TIMESTAMP),
(29, 25, 20, 1, CURRENT_TIMESTAMP),
(30, 30, 25, 1, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Disponibilidad de mesas para reservas (días verdes)
-- Rango del 02/11 al 08/11/2026
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
-- Menú semanal vigente
-- -----------------------------------------------------
INSERT IGNORE INTO `menu_semanal` (`id_menu_semanal`, `semana_inicio`, `semana_fin`, `activo`, `creado_por`, `fecha_creacion`) VALUES
(1, '2026-11-02', '2026-11-08', 1, 1, CURRENT_TIMESTAMP);

INSERT IGNORE INTO `menu_semanal_items` (`id_menu_semanal`, `id_producto`, `disponible`, `precio_semanal`, `fecha_creacion`) VALUES
(1, 7, 1, 13.00, CURRENT_TIMESTAMP),
(1, 8, 1, 15.00, CURRENT_TIMESTAMP),
(1, 9, 1, 11.00, CURRENT_TIMESTAMP),
(1, 16, 1, 4.50, CURRENT_TIMESTAMP),
(1, 24, 1, 2.50, CURRENT_TIMESTAMP);

-- -----------------------------------------------------
-- Pedidos de prueba para generar datos de "más vendidos"
-- -----------------------------------------------------
-- Eliminar pedidos y detalles existentes para empezar limpio
DELETE FROM `detalle_pedidos`;
DELETE FROM `pedidos`;

INSERT INTO `pedidos` (`id_pedido`, `id_mesa`, `id_mesero`, `id_usuario`, `fecha_pedido`, `estado`, `total`, `tipo_pedido`, `seña`, `estado_seña`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(101, 1, 1, 2, '2026-10-25 13:30:00', 'entregado', 28.00, 'con_reserva', 14.00, 'pagada', '2026-10-25 13:30:00', '2026-10-25 14:15:00'),
(102, 2, 1, 2, '2026-10-26 14:00:00', 'entregado', 16.00, 'con_reserva', 8.00, 'pagada', '2026-10-26 14:00:00', '2026-10-26 15:00:00'),
(103, 3, 1, 2, '2026-10-27 20:00:00', 'entregado', 31.00, 'con_reserva', 15.50, 'pagada', '2026-10-27 20:00:00', '2026-10-27 21:00:00'),
(104, NULL, NULL, 2, '2026-10-28 13:00:00', 'entregado', 9.50, 'para_llevar', 4.75, 'pagada', '2026-10-28 13:00:00', '2026-10-28 13:45:00'),
(105, 4, 1, 3, '2026-10-29 19:00:00', 'entregado', 42.00, 'con_reserva', 21.00, 'pagada', '2026-10-29 19:00:00', '2026-10-29 20:30:00'),
(106, NULL, NULL, 3, '2026-10-30 12:30:00', 'entregado', 11.00, 'para_llevar', 5.50, 'pagada', '2026-10-30 12:30:00', '2026-10-30 13:15:00'),
(107, 5, 1, 2, '2026-10-31 21:00:00', 'entregado', 25.00, 'con_reserva', 12.50, 'pagada', '2026-10-31 21:00:00', '2026-10-31 22:00:00'),
(108, NULL, NULL, 3, '2026-11-01 13:45:00', 'pendiente', 18.00, 'domicilio', 9.00, 'pendiente', '2026-11-01 13:45:00', '2026-11-01 13:45:00'),
(109, 6, 1, 2, '2026-11-02 14:00:00', 'pendiente', 34.00, 'con_reserva', 17.00, 'pendiente', '2026-11-02 14:00:00', '2026-11-02 14:00:00'),
(110, NULL, NULL, 2, '2026-11-02 19:30:00', 'pendiente', 22.00, 'para_llevar', 11.00, 'pendiente', '2026-11-02 19:30:00', '2026-11-02 19:30:00');

-- Detalle de pedidos para generar datos de "más vendidos"
INSERT INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
-- Pedido 101: 2x Lomo saltado (id 7)
(101, 101, 7, 2, 14.00),
-- Pedido 102: 1x Arroz con mariscos (id 8)
(102, 102, 8, 1, 16.00),
-- Pedido 103: 1x Lomo saltado + 1x Milanesa + 1x Tiramisu
(103, 103, 7, 1, 14.00),
(104, 103, 10, 1, 11.00),
(105, 103, 16, 1, 5.00),
-- Pedido 104: 1x Calamares
(106, 104, 4, 1, 9.50),
-- Pedido 105: 2x Lomo saltado + 1x Tiramisu + 1x Jugo natural
(107, 105, 7, 2, 14.00),
(108, 105, 16, 1, 5.00),
(109, 105, 24, 1, 3.00),
-- Pedido 106: 1x Milanesa napolitana
(110, 106, 10, 1, 11.00),
-- Pedido 107: 1x Salmón a la plancha + 1x Flan de café
(111, 107, 11, 1, 18.00),
(112, 107, 17, 1, 4.00),
-- Pedido 108: 1x Arroz con mariscos + 1x Mojito
(113, 108, 8, 1, 16.00),
(114, 108, 22, 1, 7.00),
-- Pedido 109: 1x Lomo saltado + 1x Lasaña vegetal + 1x Cheesecake
(115, 109, 7, 1, 14.00),
(116, 109, 9, 1, 12.00),
(117, 109, 18, 1, 6.00),
-- Pedido 110: 2x Milanesa napolitana + 1x Helado
(118, 110, 10, 2, 11.00),
(119, 110, 19, 1, 4.50),
-- Pedido 111: 3x Lomo saltado (más ventas)
(120, 103, 7, 3, 14.00),
(121, 103, 9, 1, 12.00),
(122, 109, 7, 1, 14.00),
-- Pedido 112: 2x Milanesa napolitana
(123, 110, 10, 2, 11.00),
(124, 110, 19, 1, 4.50),
-- Pedido 113: 1x Tiramisu + 1x Helado
(125, 109, 16, 1, 5.00),
(126, 110, 19, 1, 4.50),
-- Pedido 114: 1x Arroz con mariscos + 1x Jugo natural
(127, 108, 8, 1, 16.00),
(128, 108, 24, 1, 3.00),
-- Pedido 115: 2x Milanesa napolitana + 1x Helado
(129, 110, 10, 2, 11.00),
(130, 110, 19, 1, 4.50);
