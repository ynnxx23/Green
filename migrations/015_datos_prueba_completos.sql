-- ---------------------------------------------------------------------------
-- Migración 015: Datos de prueba completos
-- Asigna imágenes a productos y crea ventas para "más vendidos"
-- Las imágenes están en /public/assets/img/platos/
-- ---------------------------------------------------------------------------

-- Asignar imágenes a productos (usando imágenes de /imagenes movidas a public/assets/img/platos/)
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/bruschetta.jpeg' WHERE `id_producto` = 1;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/carpaccio.jpeg' WHERE `id_producto` = 2;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/croquetas.jpeg' WHERE `id_producto` = 3;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/calamares.jpeg' WHERE `id_producto` = 4;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/nachos.jpeg' WHERE `id_producto` = 5;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/alitas.jpeg' WHERE `id_producto` = 6;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/lomo_saltado.jpeg' WHERE `id_producto` = 7;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/arroz_mariscos.jpeg' WHERE `id_producto` = 8;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/lasana_vegetal.jpeg' WHERE `id_producto` = 9;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/milanesa.jpeg' WHERE `id_producto` = 10;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/salmon.jpeg' WHERE `id_producto` = 11;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/pasta_bolognesa.jpeg' WHERE `id_producto` = 12;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/risotto.jpeg' WHERE `id_producto` = 13;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/chuletas_cerdo.jpeg' WHERE `id_producto` = 14;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/pollo_curry.jpeg' WHERE `id_producto` = 15;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/tiramisu.jpeg' WHERE `id_producto` = 16;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/flan_cafe.jpeg' WHERE `id_producto` = 17;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/cheesecake_frutilla.jpeg' WHERE `id_producto` = 18;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/helado_artesanal.jpeg' WHERE `id_producto` = 19;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/brownie.jpeg' WHERE `id_producto` = 20;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/mousse_chocolate.jpeg' WHERE `id_producto` = 21;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/mojito.jpeg' WHERE `id_producto` = 22;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/pina_colada.jpeg' WHERE `id_producto` = 23;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/jugo_naranja.jpeg' WHERE `id_producto` = 24;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/agua_mineral.jpeg' WHERE `id_producto` = 25;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/vino_tinto.jpeg' WHERE `id_producto` = 26;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/cerveza_artesanal.jpeg' WHERE `id_producto` = 27;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/batido_fresa.jpeg' WHERE `id_producto` = 28;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/te_helado_durazno.jpeg' WHERE `id_producto` = 29;
UPDATE `productos` SET `imagen_url` = '/public/assets/img/platos/cafe_expreso.jpeg' WHERE `id_producto` = 30;

-- ---------------------------------------------------------------------------
-- Crear ventas de prueba para los últimos 30 días
-- Esto hará que aparezcan productos en "más vendidos"
-- ---------------------------------------------------------------------------

-- Eliminar pedidos y detalles existentes para empezar limpio
DELETE FROM `detalle_pedidos`;
DELETE FROM `pedidos`;

-- Insertar pedidos con productos más vendidos (los más populares tendrán más ventas)
-- Cada registro es un pedido con sus detalles
-- Usamos fechas de los últimos 30 días

-- Día 1: 2026-10-05
INSERT INTO `pedidos` (`id_pedido`, `id_mesa`, `id_mesero`, `id_usuario`, `fecha_pedido`, `estado`, `total`, `tipo_pedido`, `seña`, `estado_seña`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(101, 1, 1, 2, '2026-10-05 12:30:00', 'entregado', 28.00, 'con_reserva', 14.00, 'pagada', '2026-10-25 13:30:00', '2026-10-25 14:15:00'),
(102, 2, 1, 2, '2026-10-25 14:00:00', 'entregado', 16.00, 'con_reserva', 8.00, 'pagada', '2026-10-25 14:00:00', '2026-10-25 15:00:00'),
(103, 3, 1, 2, '2026-10-27 20:00:00', 'entregado', 31.00, 'con_reserva', 15.50, 'pagada', '2026-10-27 20:00:00', '2026-10-27 21:00:00'),
(104, NULL, NULL, 2, '2026-10-28 13:00:00', 'entregado', 9.50, 'para_llevar', 4.75, 'pagada', '2026-10-28 13:00:00', '2026-10-28 13:45:00'),
(105, 4, 1, 3, '2026-10-29 19:00:00', 'entregado', 42.00, 'con_reserva', 21.00, 'pagada', '2026-10-29 19:00:00', '2026-10-29 20:30:00'),
(106, NULL, NULL, 3, '2026-10-30 12:30:00', 'entregado', 11.00, 'para_llevar', 5.50, 'pagada', '2026-10-30 12:30:00', '2026-10-30 13:15:00'),
(107, 5, 1, 2, '2026-10-31 21:00:00', 'entregado', 25.00, 'con_reserva', 12.50, 'pagada', '2026-10-31 21:00:00', '2026-10-31 22:00:00'),
(108, NULL, NULL, 3, '2026-11-01 13:45:00', 'pendiente', 18.00, 'domicilio', 9.00, 'pendiente', '2026-11-01 13:45:00', '2026-11-01 13:45:00'),
(109, 6, 1, 2, '2026-11-02 14:00:00', 'pendiente', 34.00, 'con_reserva', 17.00, 'pendiente', '2026-11-02 14:00:00', '2026-11-02 14:00:00'),
(110, NULL, NULL, 2, '2026-11-02 19:30:00', 'pendiente', 22.00, 'para_llevar', 11.00, 'pendiente', '2026-11-02 19:30:00', '2026-11-02 19:30:00');

-- Detalle de pedidos para generar ventas
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
-- Pedido 107: 1x Salmon + 1x Flan de cafe
(111, 107, 11, 1, 18.00),
(112, 107, 17, 1, 4.00),
-- Pedido 108: 1x Arroz con mariscos + 1x Mojito
(113, 108, 8, 1, 16.00),
(114, 108, 22, 1, 7.00),
-- Pedido 109: 1x Lomo saltado + 1x Lasana vegetal + 1x Cheesecake
(115, 109, 7, 1, 14.00),
(116, 109, 9, 1, 12.00),
(117, 109, 18, 1, 6.00),
-- Pedido 110: 2x Milanesa napolitana + 1x Helado
(118, 110, 10, 2, 11.00),
(119, 110, 19, 1, 4.50),
-- Pedido 111: 3x Lomo saltado (mas ventas para este producto)
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
