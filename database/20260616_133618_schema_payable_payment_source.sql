-- ════════════════════════════════════════════════════════════════
--  Origen del pago de Cuentas por Pagar.
--  Permite registrar de dónde sale el dinero del pago:
--    - 'caja'      → caja abierta del personal logueado (crea CashMovement)
--    - 'tesoreria' → cuenta de tesorería elegida (crea TreasuryMovement)
--  Se guardan los IDs de los movimientos generados para poder revertirlos
--  al eliminar el pago.
-- ════════════════════════════════════════════════════════════════

ALTER TABLE `accounts_payable_payments`
    ADD COLUMN `source`               ENUM('caja','tesoreria') NULL AFTER `payment_method`,
    ADD COLUMN `treasury_account_id`  BIGINT UNSIGNED NULL AFTER `source`,
    ADD COLUMN `cash_session_id`      BIGINT UNSIGNED NULL AFTER `treasury_account_id`,
    ADD COLUMN `cash_movement_id`     BIGINT UNSIGNED NULL AFTER `cash_session_id`,
    ADD COLUMN `treasury_movement_id` BIGINT UNSIGNED NULL AFTER `cash_movement_id`,
    ADD INDEX `idx_app_treasury_account` (`treasury_account_id`),
    ADD INDEX `idx_app_cash_session`     (`cash_session_id`),
    ADD CONSTRAINT `fk_app_treasury_account` FOREIGN KEY (`treasury_account_id`) REFERENCES `treasury_accounts`(`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_app_cash_session`     FOREIGN KEY (`cash_session_id`)     REFERENCES `cash_sessions`(`id`)     ON DELETE SET NULL;
