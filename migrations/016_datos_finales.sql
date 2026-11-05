-- ---------------------------------------------------------------------------
-- Migración 016: Datos finales - Asegura que todo esté cargado
-- Esta migración es IDEMPOTENTE: puede ejecutarse múltiples veces sin error
-- Usa INSERT IGNORE para evitar duplicados
-- ---------------------------------------------------------------------------

-- -----------------------------------------------------
-- Áreas del restaurante (idempotente)
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
-- Productos con imágenes correctas
-- Las imágenes están en /assets/img/platos/
-- -----------------------------------------------------
INSERT IGNORE INTO `productos` (`id_producto`, `nombre`, `categoria`, `precio`, `imagen_url`, `activo`, `fecha_creacion`) VALUES
-- Entradas
(1, 'Bruschetta de tomate y albahaca', 'Entradas', 4.50, '/assets/img/platos/pan.jpeg', 1, CURRENT_TIMESTAMP),
(2, 'Carpaccio de res', 'Entradas', 8.00, '/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(3, 'Croquetas de jamón', 'Entradas', 6.50, '/assets/img/platos/sanguche_medialunas.jpeg', 1, CURRENT_TIMESTAMP),
(4, 'Calamares a la romana', 'Entradas', 9.50, '/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(5, 'Nachos con queso y guacamole', 'Entradas', 7.00, '/assets/img/platos/sanguche_medialunas.jpeg', 1, CURRENT_TIMESTAMP),
(6, 'Alitas al estilo bufalo', 'Entradas', 8.50, '/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
-- Principales
(7, 'Lomo saltado', 'Principales', 14.00, '/assets/img/platos/lomito_papas.jpeg', 1, CURRENT_TIMESTAMP),
(8, 'Arroz con mariscos', 'Principales', 16.00, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(9, 'Lasaña vegetal', 'Principales', 12.00, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(10, 'Milanesa napolitana', 'Principales', 11.00, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(11, 'Salmón a la plancha con verduras', 'Principales', 18.00, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(12, 'Pasta a la bolognesa', 'Principales', 10.50, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
(13, 'Risotto de setas', 'Principales', 13.00, '/assets/img/platos/pan.jpeg', 1, CURRENT_TIMESTAMP),
(14, 'Chuletas de cerdo con puré', 'Principales', 15.00, '/assets/img/platos/hamburguesa.jpeg', 1, CURRENT_TIMESTAMP),
(15, 'Pollo al curry con arroz basmati', 'Principales', 13.50, '/assets/img/platos/milanesanapolitanaypure.jpeg', 1, CURRENT_TIMESTAMP),
-- Postres
(16, 'Tiramisú', 'Postres', 5.00, '/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(17, 'Flan de café', 'Postres', 4.00, '/assets/img/platos/cafe.jpeg', 1, CURRENT_TIMESTAMP),
(18, 'Cheesecake de frutos rojos', 'Postres', 6.00, '/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(19, 'Helado artesanal', 'Postres', 4.50, '/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(20, 'Brownie de chocolate', 'Postres', 5.50, '/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
(21, 'Mousse de chocolate', 'Postres', 5.00, '/assets/img/platos/postre_frutilla.jpeg', 1, CURRENT_TIMESTAMP),
-- Bebidas / Coctelería
(22, 'Mojito clásico', 'Bebidas', 7.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(23, 'Piña colada', 'Bebidas', 8.00, '/assets/img/platos/jugos.jpeg', 1, CURRENT_TIMESTAMP),
(24, 'Jugo natural de naranja', 'Bebidas', 3.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(25, 'Agua mineral', 'Bebidas', 2.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(26, 'Vino tinto (copa)', 'Bebidas', 6.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(27, 'Cerveza artesanal', 'Bebidas', 5.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(28, 'Batido de fresa', 'Bebidas', 5.50, '/assets/img/platos/jugos.jpeg', 1, CURRENT_TIMESTAMP),
(29, 'Té helado de durazno', 'Bebidas', 4.00, '/assets/img/platos/jugo_naranja.jpeg', 1, CURRENT_TIMESTAMP),
(30, 'Café expreso', 'Bebidas', 3.50, '/assets/img/platos/cafe.jpeg', 1, CURRENT_TIMESTAMP);

-- Actualizar imagen_url para productos existentes (UPDATE idempotente)
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/pan.jpeg' WHERE `id_producto` = 1;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/hamburguesa.jpeg' WHERE `id_producto` = 2;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/sanguche_medialunas.jpeg' WHERE `id_producto` = 3;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/hamburguesa.jpeg' WHERE `id_producto` = 4;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/sanguche_medialunas.jpeg' WHERE `id_producto` = 5;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/hamburguesa.jpeg' WHERE `id_producto` = 6;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/lomito_papas.jpeg' WHERE `id_producto` = 7;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 8;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 9;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 10;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 11;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 12;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/pan.jpeg' WHERE `id_producto` = 13;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/hamburguesa.jpeg' WHERE `id_producto` = 14;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/milanesanapolitanaypure.jpeg' WHERE `id_producto` = 15;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/postre_frutilla.jpeg' WHERE `id_producto` = 16;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/cafe.jpeg' WHERE `id_producto` = 17;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/postre_frutilla.jpeg' WHERE `id_producto` = 18;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/postre_frutilla.jpeg' WHERE `id_producto` = 19;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/postre_frutilla.jpeg' WHERE `id_producto` = 20;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/postre_frutilla.jpeg' WHERE `id_producto` = 21;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 22;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugos.jpeg' WHERE `id_producto` = 23;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 24;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 25;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 26;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 27;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugos.jpeg' WHERE `id_producto` = 28;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 29;
UPDATE `productos` SET `imagen_url` = '/assets/img/platos/cafe.jpeg' WHERE `id_producto` = 30;

-- -----------------------------------------------------
-- Stock y límite de venta
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
-- Pedidos de prueba (para que haya datos en "más vendidos")
-- Usamos INSERT IGNORE para evitar duplicados si ya existen
-- -----------------------------------------------------
INSERT IGNORE INTO `pedidos` (`id_pedido`, `id_mesa`, `id_mesero`, `id_usuario`, `fecha_pedido`, `estado`, `total`, `tipo_pedido`, `seña`, `estado_seña`, `fecha_creacion`, `fecha_actualizacion`) VALUES
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

-- Detalle de pedidos (usar INSERT IGNORE para evitar duplicados)
INSERT IGNORE INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(101, 101, 7, 2, 14.00),
(102, 102, 8, 1, 16.00),
(103, 103, 7, 1, 14.00),
(104, 103, 10, 1, 11.00),
(105, 103, 16, 1, 5.00),
(106, 104, 4, 1, 9.50),
(107, 105, 7, 2, 14.00),
(108, 105, 16, 1, 5.00),
(109, 105, 24, 1, 3.00),
(110, 106, 10, 1, 11.00),
(111, 107, 11, 1, 18.00),
(112, 107, 17, 1, 4.00),
(113, 108, 8, 1, 16.00),
(114, 108, 22, 1, 7.00),
(115, 109, 7, 1, 14.00),
(116, 109, 9, 1, 12.00),
(117, 109, 18, 1, 6.00),
(118, 110, 10, 2, 11.00),
(119, 110, 19, 1, 4.50),
(120, 103, 7, 3, 14.00),
(121, 103, 9, 1, 12.00),
(122, 109, 7, 1, 14.00),
(123, 110, 10, 2, 11.00),
(124, 110, 19, 1, 4.50),
(125, 109, 16, 1, 5.00),
(126, 110, 19, 1, 4.50),
(127, 108, 8, 1, 16.00),
(128, 108, 24, 1, 3.00),
(129, 110, 10, 2, 11.00),
(130, 110, 19, 1, 4.50);
