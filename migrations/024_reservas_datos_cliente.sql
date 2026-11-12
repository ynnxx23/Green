-- ---------------------------------------------------------------------------
-- Migración 024: Datos del cliente en la reserva (comensales y teléfono)
-- Permite que la reserva sea creada directamente por el cliente con todos sus
-- datos, sin depender de un perfil de usuario previo.
-- ---------------------------------------------------------------------------

ALTER TABLE `reservas`
    ADD COLUMN IF NOT EXISTS `comensales` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id_usuario`;

ALTER TABLE `reservas`
    ADD COLUMN IF NOT EXISTS `telefono` VARCHAR(30) NULL AFTER `comensales`;

ALTER TABLE `reservas`
    ADD COLUMN IF NOT EXISTS `ocasion` VARCHAR(100) NULL AFTER `telefono`;

-- Telefono del cliente en su cuenta (reservas hechas desde la web publica)
ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `telefono` VARCHAR(30) NULL AFTER `email`;

-- Observaciones del pedido a domicilio
ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS `observaciones` VARCHAR(255) NULL AFTER `fecha_pedido_hora`;

-- ============================================================================
-- FIN DE MIGRACIÓN 024
-- ============================================================================
