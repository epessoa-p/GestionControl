-- ── Actualizaciones al sistema de cajas ──────────────────────────────────────
-- Ejecutar después de schema_crm_modules.sql
-- Aplica los cambios del documento cash-register-system.md

-- 1. Personal asignado a la caja física (cajero responsable permanente)
ALTER TABLE `cash_registers`
    ADD COLUMN `assigned_personal_id` BIGINT UNSIGNED NULL AFTER `branch_id`,
    ADD CONSTRAINT `fk_cash_reg_assigned_personal`
        FOREIGN KEY (`assigned_personal_id`) REFERENCES `personals`(`id`) ON DELETE SET NULL;

-- 2. Cierre de sesión: desglose por denominaciones + notas separadas
ALTER TABLE `cash_sessions`
    ADD COLUMN `closing_breakdown` JSON NULL AFTER `closing_amount`,
    ADD COLUMN `opening_notes` VARCHAR(500) NULL AFTER `notes`,
    ADD COLUMN `closing_notes` VARCHAR(500) NULL AFTER `opening_notes`;

-- 3. Categoría y fecha explícita en movimientos
ALTER TABLE `cash_movements`
    ADD COLUMN `category` VARCHAR(60) NULL AFTER `type`,
    ADD COLUMN `movement_date` TIMESTAMP NULL AFTER `reference`;
