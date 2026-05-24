-- ============================================================
-- Módulo: Recetas de producción
-- Descripción: Define qué materias primas y en qué cantidades
--              se necesitan para producir un producto final.
-- ============================================================

-- ─── Tabla principal de recetas ───
CREATE TABLE IF NOT EXISTS `recipes` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`       BIGINT UNSIGNED NOT NULL,
    `product_id`       BIGINT UNSIGNED NOT NULL,           -- Producto final que genera la receta
    `name`             VARCHAR(255)     NOT NULL,           -- Nombre descriptivo de la receta
    `recipe_number`    VARCHAR(50)      NULL,               -- Número autogenerado: REC-YYYYMM-XXXX
    `quantity_produced` DECIMAL(12,2)   NOT NULL DEFAULT 1, -- Cantidad que produce esta receta
    `status`           ENUM('borrador','activa','inactiva') NOT NULL DEFAULT 'borrador',
    `description`      TEXT             NULL,
    `created_by`       BIGINT UNSIGNED  NOT NULL,
    `created_at`       TIMESTAMP        NULL,
    `updated_at`       TIMESTAMP        NULL,
    `deleted_at`       TIMESTAMP        NULL,               -- SoftDelete

    INDEX        `idx_recipes_company`  (`company_id`),
    INDEX        `idx_recipes_product`  (`product_id`),
    INDEX        `idx_recipes_status`   (`status`),
    UNIQUE INDEX `idx_recipes_number`   (`company_id`, `recipe_number`),

    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Ingredientes de la receta (materias primas) ───
CREATE TABLE IF NOT EXISTS `recipe_items` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recipe_id`  BIGINT UNSIGNED NOT NULL,   -- Receta a la que pertenece
    `product_id` BIGINT UNSIGNED NOT NULL,   -- Materia prima requerida
    `quantity`   DECIMAL(12,2)   NOT NULL,   -- Cantidad necesaria
    `unit_cost`  DECIMAL(12,2)   NOT NULL DEFAULT 0, -- Costo unitario referencial
    `total_cost` DECIMAL(14,2)   NOT NULL DEFAULT 0, -- quantity × unit_cost
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,
    `deleted_at` TIMESTAMP       NULL,               -- SoftDelete

    INDEX `idx_recipe_items_recipe`  (`recipe_id`),
    INDEX `idx_recipe_items_product` (`product_id`),

    FOREIGN KEY (`recipe_id`)  REFERENCES `recipes`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
