-- ══════════════════════════════════════════════════════════════
-- Módulo: Costos Indirectos de Producción (Overhead)
-- Incluye: maquinaria, períodos de gastos, ítems, asignaciones
-- y configuración de distribución por empresa.
-- Ejecutar en la base de datos del proyecto.
-- ══════════════════════════════════════════════════════════════

-- ── Maquinaria y activos productivos ──────────────────────────
CREATE TABLE IF NOT EXISTS `machinery` (
    `id`                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`         BIGINT UNSIGNED NOT NULL,
    `name`               VARCHAR(255)    NOT NULL,
    `description`        TEXT            NULL,
    `cost`               DECIMAL(14,2)   NOT NULL DEFAULT 0,
    `useful_life_months` INT UNSIGNED    NOT NULL DEFAULT 12,
    `purchase_date`      DATE            NOT NULL,
    `active`             TINYINT(1)      NOT NULL DEFAULT 1,
    `created_by`         BIGINT UNSIGNED NOT NULL,
    `created_at`         TIMESTAMP       NULL,
    `updated_at`         TIMESTAMP       NULL,
    `deleted_at`         TIMESTAMP       NULL,
    INDEX `idx_machinery_company` (`company_id`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Períodos de gastos indirectos ─────────────────────────────
CREATE TABLE IF NOT EXISTS `overhead_periods` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`   BIGINT UNSIGNED NOT NULL,
    `name`         VARCHAR(100)    NOT NULL,
    `period_start` DATE            NOT NULL,
    `period_end`   DATE            NOT NULL,
    `status`       ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
    `total_amount` DECIMAL(14,2)   NOT NULL DEFAULT 0,
    `created_by`   BIGINT UNSIGNED NOT NULL,
    `created_at`   TIMESTAMP       NULL,
    `updated_at`   TIMESTAMP       NULL,
    `deleted_at`   TIMESTAMP       NULL,
    INDEX `idx_overhead_periods_company` (`company_id`),
    INDEX `idx_overhead_periods_status`  (`status`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ítems del período (líneas de gasto) ───────────────────────
CREATE TABLE IF NOT EXISTS `overhead_items` (
    `id`                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `overhead_period_id` BIGINT UNSIGNED NOT NULL,
    `machinery_id`       BIGINT UNSIGNED NULL,
    `concept`            VARCHAR(255)    NOT NULL,
    `category`           ENUM('servicio','mano_de_obra','transporte','depreciacion','otro') NOT NULL DEFAULT 'otro',
    `amount`             DECIMAL(14,2)   NOT NULL DEFAULT 0,
    `created_at`         TIMESTAMP       NULL,
    `updated_at`         TIMESTAMP       NULL,
    `deleted_at`         TIMESTAMP       NULL,
    INDEX `idx_overhead_items_period`    (`overhead_period_id`),
    INDEX `idx_overhead_items_machinery` (`machinery_id`),
    FOREIGN KEY (`overhead_period_id`) REFERENCES `overhead_periods`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`machinery_id`)       REFERENCES `machinery`(`id`)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Asignaciones de overhead a producciones ───────────────────
CREATE TABLE IF NOT EXISTS `overhead_allocations` (
    `id`                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `production_id`      BIGINT UNSIGNED NOT NULL,
    `overhead_period_id` BIGINT UNSIGNED NULL,
    `amount`             DECIMAL(14,2)   NOT NULL DEFAULT 0,
    `method`             ENUM('por_unidades','por_orden','tasa_fija','manual') NOT NULL,
    `notes`              TEXT            NULL,
    `created_at`         TIMESTAMP       NULL,
    `updated_at`         TIMESTAMP       NULL,
    `deleted_at`         TIMESTAMP       NULL,
    INDEX `idx_overhead_alloc_prod`   (`production_id`),
    INDEX `idx_overhead_alloc_period` (`overhead_period_id`),
    FOREIGN KEY (`production_id`)      REFERENCES `productions`(`id`)       ON DELETE CASCADE,
    FOREIGN KEY (`overhead_period_id`) REFERENCES `overhead_periods`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Configuración de distribución por empresa ─────────────────
ALTER TABLE `companies`
    ADD COLUMN IF NOT EXISTS `overhead_distribution_method`
        ENUM('por_unidades','por_orden','tasa_fija','manual')
        NOT NULL DEFAULT 'manual'
        AFTER `active`,
    ADD COLUMN IF NOT EXISTS `overhead_fixed_rate`
        DECIMAL(12,4) NOT NULL DEFAULT 0
        AFTER `overhead_distribution_method`;
