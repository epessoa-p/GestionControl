CREATE TABLE IF NOT EXISTS `warehouse_product_stocks` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`   BIGINT UNSIGNED NOT NULL,
    `warehouse_id` BIGINT UNSIGNED NOT NULL,
    `product_id`   BIGINT UNSIGNED NOT NULL,
    `quantity`     DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `updated_at`   TIMESTAMP       NULL,
    UNIQUE KEY `uq_wps_warehouse_product` (`warehouse_id`, `product_id`),
    INDEX `idx_wps_company`   (`company_id`),
    INDEX `idx_wps_product`   (`product_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`)   REFERENCES `products`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `productions`
    ADD COLUMN `warehouse_id` BIGINT UNSIGNED NULL AFTER `product_id`,
    ADD CONSTRAINT `fk_productions_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL;
