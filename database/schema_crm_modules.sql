-- ============================================================
-- Schema CRM Modules - Control y Gestión
-- Execute after base schema is in place
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ─── Producto: columnas adicionales para inventario ───
ALTER TABLE products
    ADD COLUMN min_stock DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER price,
    ADD COLUMN current_stock DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER min_stock,
    ADD COLUMN category ENUM('product','raw_material','supply') NOT NULL DEFAULT 'product' AFTER current_stock;

-- ─── Seguimientos (Trackings) ───
CREATE TABLE IF NOT EXISTS trackings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type ENUM('operation','client','sale','internal') NOT NULL DEFAULT 'internal',
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    due_date DATE NULL,
    completed_at TIMESTAMP NULL,
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_trackings_company (company_id),
    INDEX idx_trackings_status (status),
    INDEX idx_trackings_assigned (assigned_to),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Entradas (Entries) ───
CREATE TABLE IF NOT EXISTS entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    entry_number VARCHAR(50) NOT NULL,
    supplier VARCHAR(255) NULL,
    entry_date DATE NOT NULL,
    notes TEXT NULL,
    status ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'draft',
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_entries_company (company_id),
    INDEX idx_entries_warehouse (warehouse_id),
    INDEX idx_entries_date (entry_date),
    UNIQUE INDEX idx_entries_number (company_id, entry_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entry_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_entry_details_entry (entry_id),
    FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Salidas (Departures) ───
CREATE TABLE IF NOT EXISTS departures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    departure_number VARCHAR(50) NOT NULL,
    reason ENUM('sale','production','transfer','damage','other') NOT NULL DEFAULT 'other',
    departure_date DATE NOT NULL,
    notes TEXT NULL,
    status ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'draft',
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    reference_type VARCHAR(100) NULL,
    reference_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_departures_company (company_id),
    INDEX idx_departures_warehouse (warehouse_id),
    INDEX idx_departures_date (departure_date),
    UNIQUE INDEX idx_departures_number (company_id, departure_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS departure_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    departure_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_departure_details_departure (departure_id),
    FOREIGN KEY (departure_id) REFERENCES departures(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Cajas (Cash Registers) ───
CREATE TABLE IF NOT EXISTS cash_registers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_cash_registers_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Sesiones de Caja (Cash Sessions) ───
CREATE TABLE IF NOT EXISTS cash_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_register_id BIGINT UNSIGNED NOT NULL,
    personal_id BIGINT UNSIGNED NOT NULL,
    opening_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    closing_amount DECIMAL(14,2) NULL,
    expected_amount DECIMAL(14,2) NULL,
    difference DECIMAL(14,2) NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    opened_at TIMESTAMP NOT NULL,
    closed_at TIMESTAMP NULL,
    notes TEXT NULL,
    opened_by BIGINT UNSIGNED NOT NULL,
    closed_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_cash_sessions_register (cash_register_id),
    INDEX idx_cash_sessions_personal (personal_id),
    INDEX idx_cash_sessions_status (status),
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE,
    FOREIGN KEY (personal_id) REFERENCES personals(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Movimientos de Caja ───
CREATE TABLE IF NOT EXISTS cash_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_session_id BIGINT UNSIGNED NOT NULL,
    type ENUM('income','expense') NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    concept VARCHAR(255) NOT NULL,
    payment_method ENUM('cash','card','transfer','other') NOT NULL DEFAULT 'cash',
    reference VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_cash_movements_session (cash_session_id),
    FOREIGN KEY (cash_session_id) REFERENCES cash_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Caja Chica (Petty Cash) ───
CREATE TABLE IF NOT EXISTS petty_cashes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    initial_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_petty_cashes_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS petty_cash_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    petty_cash_id BIGINT UNSIGNED NOT NULL,
    type ENUM('expense','replenishment') NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    concept VARCHAR(255) NOT NULL,
    receipt_number VARCHAR(100) NULL,
    movement_date DATE NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_pcm_petty_cash (petty_cash_id),
    FOREIGN KEY (petty_cash_id) REFERENCES petty_cashes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Producción ───
CREATE TABLE IF NOT EXISTS productions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    batch_number VARCHAR(50) NULL,
    quantity_produced DECIMAL(12,2) NOT NULL,
    production_date DATE NOT NULL,
    status ENUM('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
    total_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_productions_company (company_id),
    INDEX idx_productions_product (product_id),
    INDEX idx_productions_status (status),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS production_costs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id BIGINT UNSIGNED NOT NULL,
    concept VARCHAR(255) NOT NULL,
    type ENUM('direct','indirect') NOT NULL DEFAULT 'direct',
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_production_costs_prod (production_id),
    FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS production_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity_used DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_production_materials_prod (production_id),
    FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Promotores ───
CREATE TABLE IF NOT EXISTS promoters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    personal_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_promoters_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (personal_id) REFERENCES personals(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Ventas ───
CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    warehouse_id BIGINT UNSIGNED NULL,
    promoter_id BIGINT UNSIGNED NULL,
    cash_session_id BIGINT UNSIGNED NULL,
    sale_number VARCHAR(50) NOT NULL,
    client_name VARCHAR(255) NULL,
    client_phone VARCHAR(50) NULL,
    client_document VARCHAR(50) NULL,
    sale_date DATE NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash','card','transfer','credit','other') NOT NULL DEFAULT 'cash',
    status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_sales_company (company_id),
    INDEX idx_sales_date (sale_date),
    INDEX idx_sales_promoter (promoter_id),
    INDEX idx_sales_status (status),
    UNIQUE INDEX idx_sales_number (company_id, sale_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (promoter_id) REFERENCES promoters(id) ON DELETE SET NULL,
    FOREIGN KEY (cash_session_id) REFERENCES cash_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sale_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_sale_details_sale (sale_id),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Comisiones ───
CREATE TABLE IF NOT EXISTS commissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    promoter_id BIGINT UNSIGNED NOT NULL,
    sale_id BIGINT UNSIGNED NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    paid_by BIGINT UNSIGNED NULL,
    period_start DATE NULL,
    period_end DATE NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_commissions_company (company_id),
    INDEX idx_commissions_promoter (promoter_id),
    INDEX idx_commissions_status (status),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES promoters(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
