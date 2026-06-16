-- ════════════════════════════════════════════════════════════════
--  Módulo Ventas — Cotizaciones y Devoluciones de venta
-- ════════════════════════════════════════════════════════════════

-- ── Cotizaciones de venta ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sales_quotations` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`       BIGINT UNSIGNED NOT NULL,
    `quotation_number` VARCHAR(50)  NOT NULL,
    `client_id`        BIGINT UNSIGNED NULL,
    `client_name`      VARCHAR(255) NULL,
    `client_phone`     VARCHAR(50)  NULL,
    `client_document`  VARCHAR(50)  NULL,
    `quotation_date`   DATE NOT NULL,
    `valid_until`      DATE NULL,
    `status`           ENUM('borrador','enviada','aprobada','rechazada','vencida','convertida') NOT NULL DEFAULT 'borrador',
    `subtotal`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`              DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `sale_id`          BIGINT UNSIGNED NULL,
    `notes`            TEXT NULL,
    `created_by`       BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, `deleted_at` TIMESTAMP NULL,
    UNIQUE INDEX `idx_sq_number` (`company_id`, `quotation_number`),
    INDEX `idx_sq_company` (`company_id`),
    INDEX `idx_sq_client`  (`client_id`),
    INDEX `idx_sq_status`  (`status`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`)  REFERENCES `clients`(`id`)   ON DELETE SET NULL,
    FOREIGN KEY (`sale_id`)    REFERENCES `sales`(`id`)     ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sales_quotation_items` (
    `id`                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sales_quotation_id`  BIGINT UNSIGNED NOT NULL,
    `product_id`          BIGINT UNSIGNED NOT NULL,
    `quantity`            DECIMAL(12,2) NOT NULL,
    `unit_price`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `discount`            DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`               DECIMAL(14,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL,
    INDEX `idx_sqi_quotation` (`sales_quotation_id`),
    INDEX `idx_sqi_product`   (`product_id`),
    FOREIGN KEY (`sales_quotation_id`) REFERENCES `sales_quotations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)         REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Devoluciones de venta ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sales_returns` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`     BIGINT UNSIGNED NOT NULL,
    `return_number`  VARCHAR(50) NOT NULL,
    `sale_id`        BIGINT UNSIGNED NULL,
    `client_id`      BIGINT UNSIGNED NULL,
    `client_name`    VARCHAR(255) NULL,
    `warehouse_id`   BIGINT UNSIGNED NULL,
    `return_date`    DATE NOT NULL,
    `reason`         ENUM('defectuoso','incorrecto','cliente','otro') NOT NULL DEFAULT 'otro',
    `status`         ENUM('borrador','confirmada','cancelada') NOT NULL DEFAULT 'borrador',
    `total`          DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`          TEXT NULL,
    `created_by`     BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, `deleted_at` TIMESTAMP NULL,
    UNIQUE INDEX `idx_sr_number` (`company_id`, `return_number`),
    INDEX `idx_sr_company` (`company_id`),
    INDEX `idx_sr_sale`    (`sale_id`),
    INDEX `idx_sr_status`  (`status`),
    FOREIGN KEY (`company_id`)   REFERENCES `companies`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`sale_id`)      REFERENCES `sales`(`id`)      ON DELETE SET NULL,
    FOREIGN KEY (`client_id`)    REFERENCES `clients`(`id`)    ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`)   REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sales_return_items` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sales_return_id`  BIGINT UNSIGNED NOT NULL,
    `product_id`       BIGINT UNSIGNED NOT NULL,
    `quantity`         DECIMAL(12,2) NOT NULL,
    `unit_price`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL,
    INDEX `idx_sri_return`  (`sales_return_id`),
    INDEX `idx_sri_product` (`product_id`),
    FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)      REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
