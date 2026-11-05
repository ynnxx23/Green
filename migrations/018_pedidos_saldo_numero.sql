-- ============================================================================
-- MIGRACIÓN 018: Columnas adicionales para pedidos (saldo pendiente, número de pedido)
// Rehacer sistema de platos y pedidos con seña 50%
-- Ejecutar DESPUÉS de la migración 017.
// Soporta: rehacer pedidos con seña automática, número de pedido, saldo pendiente
-- ============================================================================

-- Número de pedido legible (PED-YYYYMMDD-XXXX)
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `numero_pedido` VARCHAR(30) NULL AFTER `observaciones`;
ALTER TABLE `pedidos` ADD KEY `idx_pedido_numero` (`numero_pedido`);

-- Saldo pendiente (total - seña pagada)
ALTER TABLE `pedidos` ADD COLUMN IF NOT EXISTS `saldo_pendiente` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `estado_seña`;
ALTER TABLE `pedidos` ADD KEY `idx_pedido_saldo` (`saldo_pendiente`);

-- Actualizar saldo_pendiente en pedidos existentes: total - seña
UPDATE `pedidos` SET `saldo_pendiente` = ROUND(`total` - `seña`, 2) WHERE `saldo_pendiente` = 0 OR `saldo_pendiente` IS NULL;

-- ============================================================================
-- FIN DE MIGRACIÓN 018
-- ============================================================================
