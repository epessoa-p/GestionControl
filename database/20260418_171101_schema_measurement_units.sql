-- ================================================================
-- Script: Módulo de Unidades de Medida
-- Base de datos: MySQL 8+
-- ================================================================

START TRANSACTION;

-- 1) Crear tabla de unidades de medida
CREATE TABLE IF NOT EXISTS measurement_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    description VARCHAR(255) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY measurement_units_name_unique (name),
    UNIQUE KEY measurement_units_symbol_unique (symbol),
    KEY measurement_units_active_idx (active),
    KEY measurement_units_deleted_at_idx (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Agregar columna de relación en productos (si no existe)
SET @has_column := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'measurement_unit_id'
);
SET @sql_add_column := IF(@has_column = 0,
    'ALTER TABLE products ADD COLUMN measurement_unit_id BIGINT UNSIGNED NULL AFTER unit',
    'SELECT ''Column measurement_unit_id already exists'''
);
PREPARE stmt_add_column FROM @sql_add_column;
EXECUTE stmt_add_column;
DEALLOCATE PREPARE stmt_add_column;

-- 3) Agregar índice (si no existe)
SET @has_index := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND INDEX_NAME = 'products_measurement_unit_id_idx'
);
SET @sql_add_index := IF(@has_index = 0,
    'ALTER TABLE products ADD INDEX products_measurement_unit_id_idx (measurement_unit_id)',
    'SELECT ''Index products_measurement_unit_id_idx already exists'''
);
PREPARE stmt_add_index FROM @sql_add_index;
EXECUTE stmt_add_index;
DEALLOCATE PREPARE stmt_add_index;

-- 4) Agregar llave foránea (si no existe)
SET @has_fk := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND CONSTRAINT_NAME = 'products_measurement_unit_id_fk'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql_add_fk := IF(@has_fk = 0,
    'ALTER TABLE products ADD CONSTRAINT products_measurement_unit_id_fk FOREIGN KEY (measurement_unit_id) REFERENCES measurement_units(id) ON UPDATE CASCADE ON DELETE SET NULL',
    'SELECT ''Foreign key products_measurement_unit_id_fk already exists'''
);
PREPARE stmt_add_fk FROM @sql_add_fk;
EXECUTE stmt_add_fk;
DEALLOCATE PREPARE stmt_add_fk;

-- 5) Inserts de unidades comunes en español
INSERT INTO measurement_units (name, symbol, description, active, created_at, updated_at)
SELECT tmp.name, tmp.symbol, tmp.description, 1, NOW(), NOW()
FROM (
    SELECT 'Unidad' AS name, 'u' AS symbol, 'Unidad genérica de conteo' AS description
    UNION ALL SELECT 'Kilogramo', 'kg', 'Peso en kilogramos'
    UNION ALL SELECT 'Gramo', 'g', 'Peso en gramos'
    UNION ALL SELECT 'Tonelada', 't', 'Peso en toneladas'
    UNION ALL SELECT 'Litro', 'lt', 'Volumen en litros'
    UNION ALL SELECT 'Mililitro', 'ml', 'Volumen en mililitros'
    UNION ALL SELECT 'Metro', 'm', 'Longitud en metros'
    UNION ALL SELECT 'Centímetro', 'cm', 'Longitud en centímetros'
    UNION ALL SELECT 'Milímetro', 'mm', 'Longitud en milímetros'
    UNION ALL SELECT 'Metro cuadrado', 'm2', 'Superficie en metros cuadrados'
    UNION ALL SELECT 'Metro cúbico', 'm3', 'Volumen en metros cúbicos'
    UNION ALL SELECT 'Caja', 'caja', 'Presentación por caja'
    UNION ALL SELECT 'Paquete', 'paq', 'Presentación por paquete'
    UNION ALL SELECT 'Bolsa', 'bolsa', 'Presentación por bolsa'
    UNION ALL SELECT 'Rollo', 'rollo', 'Presentación por rollo'
    UNION ALL SELECT 'Par', 'par', 'Presentación por par'
    UNION ALL SELECT 'Docena', 'doc', 'Presentación por docena'
    UNION ALL SELECT 'Galón', 'gal', 'Volumen en galones'
    UNION ALL SELECT 'Pulgada', 'in', 'Longitud en pulgadas'
    UNION ALL SELECT 'Pie', 'ft', 'Longitud en pies'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1
    FROM measurement_units mu
    WHERE mu.name = tmp.name OR mu.symbol = tmp.symbol
);

-- 6) Intentar asociar productos existentes por coincidencia en campo unit
UPDATE products p
LEFT JOIN measurement_units mu_symbol ON LOWER(TRIM(p.unit)) = LOWER(TRIM(mu_symbol.symbol))
LEFT JOIN measurement_units mu_name ON LOWER(TRIM(p.unit)) = LOWER(TRIM(mu_name.name))
SET p.measurement_unit_id = COALESCE(mu_symbol.id, mu_name.id)
WHERE p.measurement_unit_id IS NULL
  AND p.unit IS NOT NULL
  AND TRIM(p.unit) <> '';

-- 7) Completar faltantes con Unidad (u)
UPDATE products p
JOIN measurement_units mu ON mu.symbol = 'u'
SET p.measurement_unit_id = mu.id
WHERE p.measurement_unit_id IS NULL;

COMMIT;
