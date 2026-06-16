-- Agregar foto y coordenadas GPS al cliente
ALTER TABLE `clients`
    ADD COLUMN `photo`     VARCHAR(255)    NULL AFTER `notes`,
    ADD COLUMN `latitude`  DECIMAL(10,8)   NULL AFTER `photo`,
    ADD COLUMN `longitude` DECIMAL(11,8)   NULL AFTER `latitude`;

-- Documentos adjuntos del cliente (CI, facturas, etc.)
CREATE TABLE IF NOT EXISTS `client_documents` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id`     BIGINT UNSIGNED NOT NULL,
    `type`          ENUM('ci_anverso','ci_reverso','factura','otro') NOT NULL DEFAULT 'otro',
    `filename`      VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type`     VARCHAR(100) NULL,
    `size`          INT UNSIGNED NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    INDEX `idx_client_docs_client` (`client_id`),
    INDEX `idx_client_docs_type`   (`client_id`, `type`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
