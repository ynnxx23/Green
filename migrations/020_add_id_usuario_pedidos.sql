ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `id_usuario` INT UNSIGNED NULL AFTER `id_mesero`;
ALTER TABLE `pedidos` ADD KEY `idx_pedido_usuario` (`id_usuario`);
ALTER TABLE `pedidos` ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;
