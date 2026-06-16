-- ══════════════════════════════════════════════════════════════
-- Módulo CRM: Clientes
-- Tablas: clients, client_contacts
-- Ejecutar después de schema_crm_modules.sql
-- ══════════════════════════════════════════════════════════════

-- ── Clientes ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clients` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`       BIGINT UNSIGNED NOT NULL,
    `client_number`    VARCHAR(20)     NULL,
    -- Tipo: persona natural o empresa
    `type`             ENUM('persona_natural','empresa') NOT NULL DEFAULT 'persona_natural',
    `name`             VARCHAR(255)    NOT NULL,
    `commercial_name`  VARCHAR(255)    NULL,               -- razón social / nombre comercial
    -- Documento de identidad
    `document_type`    ENUM('cedula','ruc','pasaporte','otro') NULL,
    `document_number`  VARCHAR(50)     NULL,
    -- Contacto
    `email`            VARCHAR(255)    NULL,
    `phone`            VARCHAR(30)     NULL,
    `mobile`           VARCHAR(30)     NULL,
    -- Ubicación
    `address`          TEXT            NULL,
    `city`             VARCHAR(100)    NULL,
    `country`          VARCHAR(100)    NULL DEFAULT 'Ecuador',
    -- CRM
    `status`           ENUM('activo','inactivo','prospecto','bloqueado') NOT NULL DEFAULT 'prospecto',
    `source`           ENUM('directo','referido','web','redes_sociales','feria','otro') NULL,
    `assigned_to`      BIGINT UNSIGNED NULL,               -- asesor/vendedor responsable
    `notes`            TEXT            NULL,
    -- Auditoría
    `created_by`       BIGINT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP       NULL,
    `updated_at`       TIMESTAMP       NULL,
    `deleted_at`       TIMESTAMP       NULL,

    INDEX `idx_clients_company`  (`company_id`),
    INDEX `idx_clients_status`   (`status`),
    INDEX `idx_clients_assigned` (`assigned_to`),
    INDEX `idx_clients_number`   (`client_number`),
    -- Número de documento único por empresa (cuando se informa)
    UNIQUE INDEX `idx_clients_doc` (`company_id`, `document_number`),

    FOREIGN KEY (`company_id`)  REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)     ON DELETE SET NULL,
    FOREIGN KEY (`created_by`)  REFERENCES `users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contactos adicionales del cliente ────────────────────────
-- Para clientes tipo 'empresa' con múltiples personas de contacto
CREATE TABLE IF NOT EXISTS `client_contacts` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id`  BIGINT UNSIGNED NOT NULL,
    `name`       VARCHAR(255)    NOT NULL,
    `position`   VARCHAR(100)    NULL,                     -- cargo dentro de la empresa
    `email`      VARCHAR(255)    NULL,
    `phone`      VARCHAR(30)     NULL,
    `is_primary` TINYINT(1)      NOT NULL DEFAULT 0,
    `notes`      TEXT            NULL,
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,

    INDEX `idx_client_contacts_client` (`client_id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
