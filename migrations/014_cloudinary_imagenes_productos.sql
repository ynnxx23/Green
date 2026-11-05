-- Migración 014: columnas de imagen (Cloudinary) en productos
ALTER TABLE `productos`
    ADD COLUMN `imagen_url` VARCHAR(500) NULL COMMENT 'URL segura de la imagen en Cloudinary' AFTER `precio`,
    ADD COLUMN `imagen_public_id` VARCHAR(200) NULL COMMENT 'Identificador público de la imagen en Cloudinary' AFTER `imagen_url`;
