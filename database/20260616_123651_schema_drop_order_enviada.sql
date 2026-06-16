-- ════════════════════════════════════════════════════════════════
--  Quitar el estado 'enviada' de las órdenes de compra.
--  Flujo nuevo: borrador → aprobada → (recibida_parcial) → recibida.
--  Una vez aprobada, la orden queda lista para recepcionar.
-- ════════════════════════════════════════════════════════════════

-- 1. Migrar cualquier orden existente en 'enviada' a 'aprobada'
UPDATE `purchase_orders` SET `status` = 'aprobada' WHERE `status` = 'enviada';

-- 2. Quitar 'enviada' del ENUM
ALTER TABLE `purchase_orders`
    MODIFY COLUMN `status` ENUM('borrador','aprobada','recibida_parcial','recibida','cancelada')
    NOT NULL DEFAULT 'borrador';
