-- ──────────────────────────────────────────────────────────────────────────
-- Vinculación de Ventas con Clientes CRM
-- Agrega la FK client_id nullable a la tabla sales.
-- Ejecutar después de haber corrido schema_clients.sql
-- ──────────────────────────────────────────────────────────────────────────

ALTER TABLE `sales`
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL
        AFTER `company_id`,
    ADD INDEX `idx_sales_client_id` (`client_id`),
    ADD CONSTRAINT `fk_sales_client_id`
        FOREIGN KEY (`client_id`)
        REFERENCES `clients`(`id`)
        ON DELETE SET NULL;
