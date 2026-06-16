-- ── Imágenes de productos ─────────────────────────────────────────
-- Ejecutar después de que exista la tabla `products`.
-- Nota: requiere haber ejecutado `php artisan storage:link` para que
--       las imágenes sean accesibles desde public/storage.

CREATE TABLE IF NOT EXISTS `product_images` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`    BIGINT UNSIGNED NOT NULL,
    `filename`      VARCHAR(255)    NOT NULL,          -- ruta relativa en storage/app/public
    `original_name` VARCHAR(255)    NOT NULL,          -- nombre original del archivo
    `mime_type`     VARCHAR(100)    NULL,
    `size`          INT UNSIGNED    NULL,              -- tamaño en bytes
    `is_primary`    TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order`    INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    INDEX `idx_product_images_product`  (`product_id`),
    INDEX `idx_product_images_primary`  (`product_id`, `is_primary`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
