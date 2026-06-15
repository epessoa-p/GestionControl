-- Módulo de Tesorería: cuentas bancarias y de efectivo de la empresa
-- Ejecutar con usuario con permisos DDL sobre la base de datos del proyecto

CREATE TABLE IF NOT EXISTS `treasury_accounts` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`      BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(255) NOT NULL,
    `type`            ENUM('banco','efectivo','otro') NOT NULL DEFAULT 'efectivo',
    `bank_name`       VARCHAR(100) NULL,
    `account_number`  VARCHAR(50)  NULL,
    `initial_balance` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `current_balance` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `color`           VARCHAR(20)  NOT NULL DEFAULT '#3b82f6',
    `active`          TINYINT(1)   NOT NULL DEFAULT 1,
    `notes`           TEXT NULL,
    `created_by`      BIGINT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    `deleted_at`      TIMESTAMP NULL,
    INDEX `idx_treasury_accounts_company` (`company_id`),
    INDEX `idx_treasury_accounts_active`  (`active`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `treasury_movements` (
    `id`                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `treasury_account_id`  BIGINT UNSIGNED NOT NULL,
    `company_id`           BIGINT UNSIGNED NOT NULL,
    `type`                 ENUM('entrada','salida') NOT NULL,
    `category`             VARCHAR(60)  NOT NULL DEFAULT 'otro_ingreso',
    `amount`               DECIMAL(14,2) NOT NULL,
    `description`          TEXT NULL,
    `reference`            VARCHAR(100) NULL,
    `movement_date`        DATE NOT NULL,
    `created_by`           BIGINT UNSIGNED NOT NULL,
    `created_at`           TIMESTAMP NULL,
    `updated_at`           TIMESTAMP NULL,
    `deleted_at`           TIMESTAMP NULL,
    INDEX `idx_treasury_mov_account` (`treasury_account_id`),
    INDEX `idx_treasury_mov_company` (`company_id`),
    INDEX `idx_treasury_mov_date`    (`movement_date`),
    INDEX `idx_treasury_mov_type`    (`type`),
    FOREIGN KEY (`treasury_account_id`) REFERENCES `treasury_accounts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
