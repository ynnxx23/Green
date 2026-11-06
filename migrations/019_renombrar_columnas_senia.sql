ALTER TABLE `pedidos` CHANGE COLUMN `se??a` `senia` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `pedidos` CHANGE COLUMN `estado_se??a` `estado_senia` ENUM('pendiente','pagada','devuelta') NOT NULL DEFAULT 'pendiente';
