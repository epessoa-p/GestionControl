-- ═══════════════════════════════════════════════════════════════
--  Módulo de Compras — GestionControl
--  Ejecutar en orden. Las tablas padre deben existir:
--  companies, users, products, warehouses
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Proveedores ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`       BIGINT UNSIGNED NOT NULL,
    `supplier_number`  VARCHAR(50)  NULL,
    `type`             ENUM('persona_natural','empresa') NOT NULL DEFAULT 'empresa',
    `name`             VARCHAR(255) NOT NULL,
    `commercial_name`  VARCHAR(255) NULL,
    `document_type`    ENUM('cedula','ruc','pasaporte','otro') NULL,
    `document_number`  VARCHAR(50)  NULL,
    `email`            VARCHAR(255) NULL,
    `phone`            VARCHAR(30)  NULL,
    `mobile`           VARCHAR(30)  NULL,
    `address`          TEXT NULL,
    `city`             VARCHAR(100) NULL,
    `country`          VARCHAR(100) NOT NULL DEFAULT 'Ecuador',
    `contact_name`     VARCHAR(255) NULL,
    `contact_email`    VARCHAR(255) NULL,
    `contact_phone`    VARCHAR(30)  NULL,
    `payment_terms`    INT UNSIGNED NOT NULL DEFAULT 0,
    `credit_limit`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `status`           ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    `notes`            TEXT NULL,
    `created_by`       BIGINT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL,
    `deleted_at`       TIMESTAMP NULL,
    UNIQUE INDEX `idx_suppliers_number`  (`company_id`, `supplier_number`),
    INDEX `idx_suppliers_company`        (`company_id`),
    INDEX `idx_suppliers_status`         (`status`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Solicitudes de compra ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `purchase_requests` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `request_number`  VARCHAR(50) NOT NULL,
    `requested_by`    BIGINT UNSIGNED NOT NULL,
    `department`      VARCHAR(100) NULL,
    `priority`        ENUM('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
    `expected_date`   DATE NULL,
    `status`          ENUM('borrador','pendiente','aprobada','rechazada','en_proceso','completada','cancelada') NOT NULL DEFAULT 'borrador',
    `notes`           TEXT NULL,
    `created_by`      BIGINT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    `deleted_at`      TIMESTAMP NULL,
    UNIQUE INDEX `idx_pr_number`  (`company_id`, `request_number`),
    INDEX `idx_pr_company`        (`company_id`),
    INDEX `idx_pr_status`         (`status`),
    FOREIGN KEY (`company_id`)  REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`),
    FOREIGN KEY (`created_by`)  REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_request_items` (
    `id`                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_request_id` BIGINT UNSIGNED NOT NULL,
    `product_id`          BIGINT UNSIGNED NOT NULL,
    `quantity`            DECIMAL(12,2) NOT NULL,
    `estimated_unit_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `notes`               TEXT NULL,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    INDEX `idx_pri_request` (`purchase_request_id`),
    INDEX `idx_pri_product` (`product_id`),
    FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Cotizaciones ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `purchase_quotations` (
    `id`                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`           BIGINT UNSIGNED NOT NULL,
    `quotation_number`     VARCHAR(50) NOT NULL,
    `purchase_request_id`  BIGINT UNSIGNED NULL,
    `supplier_id`          BIGINT UNSIGNED NULL,
    `quotation_date`       DATE NOT NULL,
    `valid_until`          DATE NULL,
    `status`               ENUM('borrador','enviada','recibida','aprobada','rechazada','cancelada') NOT NULL DEFAULT 'borrador',
    `subtotal`             DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`                  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`             DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`                DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`                TEXT NULL,
    `created_by`           BIGINT UNSIGNED NOT NULL,
    `created_at`           TIMESTAMP NULL,
    `updated_at`           TIMESTAMP NULL,
    `deleted_at`           TIMESTAMP NULL,
    UNIQUE INDEX `idx_pq_number`   (`company_id`, `quotation_number`),
    INDEX `idx_pq_company`         (`company_id`),
    INDEX `idx_pq_supplier`        (`supplier_id`),
    INDEX `idx_pq_status`          (`status`),
    FOREIGN KEY (`company_id`)          REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`supplier_id`)         REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`)          REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_quotation_items` (
    `id`                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_quotation_id` BIGINT UNSIGNED NOT NULL,
    `product_id`            BIGINT UNSIGNED NOT NULL,
    `quantity`              DECIMAL(12,2) NOT NULL,
    `unit_price`            DECIMAL(12,2) NOT NULL DEFAULT 0,
    `discount`              DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`                 DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`                 TEXT NULL,
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    INDEX `idx_pqi_quotation` (`purchase_quotation_id`),
    INDEX `idx_pqi_product`   (`product_id`),
    FOREIGN KEY (`purchase_quotation_id`) REFERENCES `purchase_quotations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Órdenes de Compra ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `purchase_orders` (
    `id`                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`            BIGINT UNSIGNED NOT NULL,
    `order_number`          VARCHAR(50) NOT NULL,
    `supplier_id`           BIGINT UNSIGNED NOT NULL,
    `purchase_quotation_id` BIGINT UNSIGNED NULL,
    `warehouse_id`          BIGINT UNSIGNED NOT NULL,
    `order_date`            DATE NOT NULL,
    `expected_date`         DATE NULL,
    `status`                ENUM('borrador','aprobada','enviada','recibida_parcial','recibida','cancelada') NOT NULL DEFAULT 'borrador',
    `subtotal`              DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`                   DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`              DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`                 DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`                 TEXT NULL,
    `created_by`            BIGINT UNSIGNED NOT NULL,
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,
    UNIQUE INDEX `idx_po_number`   (`company_id`, `order_number`),
    INDEX `idx_po_company`         (`company_id`),
    INDEX `idx_po_supplier`        (`supplier_id`),
    INDEX `idx_po_status`          (`status`),
    FOREIGN KEY (`company_id`)            REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`supplier_id`)           REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`purchase_quotation_id`) REFERENCES `purchase_quotations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`)          REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`created_by`)            REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id`                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_order_id` BIGINT UNSIGNED NOT NULL,
    `product_id`        BIGINT UNSIGNED NOT NULL,
    `quantity`          DECIMAL(12,2) NOT NULL,
    `quantity_received` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `unit_price`        DECIMAL(12,2) NOT NULL DEFAULT 0,
    `discount`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`             DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`             TEXT NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    INDEX `idx_poi_order`   (`purchase_order_id`),
    INDEX `idx_poi_product` (`product_id`),
    FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Recepciones ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `purchase_receptions` (
    `id`                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`        BIGINT UNSIGNED NOT NULL,
    `reception_number`  VARCHAR(50) NOT NULL,
    `purchase_order_id` BIGINT UNSIGNED NOT NULL,
    `warehouse_id`      BIGINT UNSIGNED NOT NULL,
    `reception_date`    DATE NOT NULL,
    `invoice_number`    VARCHAR(100) NULL,
    `status`            ENUM('borrador','confirmada','cancelada') NOT NULL DEFAULT 'borrador',
    `total`             DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`             TEXT NULL,
    `created_by`        BIGINT UNSIGNED NOT NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    UNIQUE INDEX `idx_prec_number` (`company_id`, `reception_number`),
    INDEX `idx_prec_company`       (`company_id`),
    INDEX `idx_prec_order`         (`purchase_order_id`),
    INDEX `idx_prec_status`        (`status`),
    FOREIGN KEY (`company_id`)        REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`),
    FOREIGN KEY (`warehouse_id`)      REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`created_by`)        REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_reception_items` (
    `id`                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_reception_id`  BIGINT UNSIGNED NOT NULL,
    `purchase_order_item_id` BIGINT UNSIGNED NOT NULL,
    `product_id`             BIGINT UNSIGNED NOT NULL,
    `quantity_ordered`       DECIMAL(12,2) NOT NULL,
    `quantity_received`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `unit_price`             DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`                  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `created_at`             TIMESTAMP NULL,
    `updated_at`             TIMESTAMP NULL,
    INDEX `idx_prei_reception` (`purchase_reception_id`),
    INDEX `idx_prei_oi`        (`purchase_order_item_id`),
    INDEX `idx_prei_product`   (`product_id`),
    FOREIGN KEY (`purchase_reception_id`)  REFERENCES `purchase_receptions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Devoluciones ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `purchase_returns` (
    `id`                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`            BIGINT UNSIGNED NOT NULL,
    `return_number`         VARCHAR(50) NOT NULL,
    `purchase_reception_id` BIGINT UNSIGNED NULL,
    `supplier_id`           BIGINT UNSIGNED NOT NULL,
    `return_date`           DATE NOT NULL,
    `reason`                ENUM('defectuoso','incorrecto','exceso','otro') NOT NULL DEFAULT 'otro',
    `status`                ENUM('borrador','confirmada','cancelada') NOT NULL DEFAULT 'borrador',
    `total`                 DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`                 TEXT NULL,
    `created_by`            BIGINT UNSIGNED NOT NULL,
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,
    UNIQUE INDEX `idx_pret_number`  (`company_id`, `return_number`),
    INDEX `idx_pret_company`        (`company_id`),
    INDEX `idx_pret_supplier`       (`supplier_id`),
    INDEX `idx_pret_status`         (`status`),
    FOREIGN KEY (`company_id`)            REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`purchase_reception_id`) REFERENCES `purchase_receptions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`supplier_id`)           REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`created_by`)            REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_return_items` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_return_id` BIGINT UNSIGNED NOT NULL,
    `product_id`       BIGINT UNSIGNED NOT NULL,
    `quantity`         DECIMAL(12,2) NOT NULL,
    `unit_price`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`            TEXT NULL,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL,
    INDEX `idx_preti_return`  (`purchase_return_id`),
    INDEX `idx_preti_product` (`product_id`),
    FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. Cuentas por Pagar ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `accounts_payable` (
    `id`                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`            BIGINT UNSIGNED NOT NULL,
    `ap_number`             VARCHAR(50) NOT NULL,
    `supplier_id`           BIGINT UNSIGNED NOT NULL,
    `purchase_order_id`     BIGINT UNSIGNED NULL,
    `purchase_reception_id` BIGINT UNSIGNED NULL,
    `invoice_number`        VARCHAR(100) NULL,
    `invoice_date`          DATE NOT NULL,
    `due_date`              DATE NOT NULL,
    `amount`                DECIMAL(14,2) NOT NULL DEFAULT 0,
    `amount_paid`           DECIMAL(14,2) NOT NULL DEFAULT 0,
    `balance`               DECIMAL(14,2) NOT NULL DEFAULT 0,
    `status`                ENUM('pendiente','pago_parcial','pagada','vencida','anulada') NOT NULL DEFAULT 'pendiente',
    `notes`                 TEXT NULL,
    `created_by`            BIGINT UNSIGNED NOT NULL,
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,
    UNIQUE INDEX `idx_ap_number`   (`company_id`, `ap_number`),
    INDEX `idx_ap_company`         (`company_id`),
    INDEX `idx_ap_supplier`        (`supplier_id`),
    INDEX `idx_ap_due_date`        (`due_date`),
    INDEX `idx_ap_status`          (`status`),
    FOREIGN KEY (`company_id`)            REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`supplier_id`)           REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`purchase_order_id`)     REFERENCES `purchase_orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`purchase_reception_id`) REFERENCES `purchase_receptions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`)            REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounts_payable_payments` (
    `id`                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `accounts_payable_id` BIGINT UNSIGNED NOT NULL,
    `amount`              DECIMAL(14,2) NOT NULL,
    `payment_date`        DATE NOT NULL,
    `payment_method`      ENUM('efectivo','transferencia','cheque','tarjeta','otro') NOT NULL DEFAULT 'efectivo',
    `reference`           VARCHAR(255) NULL,
    `notes`               TEXT NULL,
    `created_by`          BIGINT UNSIGNED NOT NULL,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    INDEX `idx_app_ap`         (`accounts_payable_id`),
    FOREIGN KEY (`accounts_payable_id`) REFERENCES `accounts_payable`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
