-- ============================================================
-- Schema: Traspasos entre Almacenes, Ordenes/Pedidos, Ventas a Crédito
-- Execute after schema_crm_modules.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ─── Traspasos entre Almacenes (Warehouse Transfers) ───
CREATE TABLE IF NOT EXISTS warehouse_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    transfer_number VARCHAR(50) NOT NULL,
    from_warehouse_id BIGINT UNSIGNED NOT NULL,
    to_warehouse_id BIGINT UNSIGNED NOT NULL,
    transfer_date DATE NOT NULL,
    status ENUM('draft','in_transit','completed','cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT NULL,
    total_items INT UNSIGNED NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_wt_company (company_id),
    INDEX idx_wt_from (from_warehouse_id),
    INDEX idx_wt_to (to_warehouse_id),
    INDEX idx_wt_status (status),
    INDEX idx_wt_date (transfer_date),
    UNIQUE INDEX idx_wt_number (company_id, transfer_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS warehouse_transfer_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    warehouse_transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_wtd_transfer (warehouse_transfer_id),
    INDEX idx_wtd_product (product_id),
    FOREIGN KEY (warehouse_transfer_id) REFERENCES warehouse_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Ordenes / Pedidos (Orders) ───
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    warehouse_id BIGINT UNSIGNED NULL,
    order_number VARCHAR(50) NOT NULL,
    order_type ENUM('purchase','customer') NOT NULL DEFAULT 'customer',
    client_name VARCHAR(255) NULL,
    client_phone VARCHAR(50) NULL,
    client_document VARCHAR(50) NULL,
    client_email VARCHAR(255) NULL,
    client_address TEXT NULL,
    order_date DATE NOT NULL,
    expected_date DATE NULL,
    delivered_date DATE NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('draft','confirmed','in_process','shipped','delivered','cancelled') NOT NULL DEFAULT 'draft',
    priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_orders_company (company_id),
    INDEX idx_orders_date (order_date),
    INDEX idx_orders_status (status),
    INDEX idx_orders_type (order_type),
    INDEX idx_orders_priority (priority),
    UNIQUE INDEX idx_orders_number (company_id, order_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_od_order (order_id),
    INDEX idx_od_product (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Ventas a Crédito (Sale Credit / Installments) ───
-- Campos adicionales en sales para soporte de crédito
ALTER TABLE sales
    ADD COLUMN sale_type ENUM('cash','credit') NOT NULL DEFAULT 'cash' AFTER payment_method,
    ADD COLUMN credit_total_installments INT UNSIGNED NULL AFTER sale_type,
    ADD COLUMN credit_paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER credit_total_installments,
    ADD COLUMN credit_status ENUM('pending','partial','paid','overdue') NULL AFTER credit_paid_amount;

-- Cuotas de crédito
CREATE TABLE IF NOT EXISTS sale_installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    installment_number INT UNSIGNED NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('pending','partial','paid','overdue') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    paid_by BIGINT UNSIGNED NULL,
    payment_method ENUM('cash','card','transfer','other') NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_si_sale (sale_id),
    INDEX idx_si_status (status),
    INDEX idx_si_due_date (due_date),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Permisos nuevos para los módulos ───
INSERT INTO permissions (name, slug, module, description, created_at, updated_at)
SELECT * FROM (
    SELECT 'Ver Traspasos' AS name, 'transfers.view' AS slug, 'transfers' AS module, 'Ver traspasos entre almacenes' AS description, NOW() AS created_at, NOW() AS updated_at UNION ALL
    SELECT 'Crear Traspasos', 'transfers.create', 'transfers', 'Crear traspasos entre almacenes', NOW(), NOW() UNION ALL
    SELECT 'Confirmar Traspasos', 'transfers.confirm', 'transfers', 'Confirmar/completar traspasos', NOW(), NOW() UNION ALL
    SELECT 'Cancelar Traspasos', 'transfers.cancel', 'transfers', 'Cancelar traspasos', NOW(), NOW() UNION ALL
    SELECT 'Eliminar Traspasos', 'transfers.delete', 'transfers', 'Eliminar traspasos', NOW(), NOW() UNION ALL
    SELECT 'Ver Ordenes', 'orders.view', 'orders', 'Ver ordenes y pedidos', NOW(), NOW() UNION ALL
    SELECT 'Crear Ordenes', 'orders.create', 'orders', 'Crear ordenes y pedidos', NOW(), NOW() UNION ALL
    SELECT 'Editar Ordenes', 'orders.edit', 'orders', 'Editar ordenes y pedidos', NOW(), NOW() UNION ALL
    SELECT 'Cambiar Estado Ordenes', 'orders.change-status', 'orders', 'Cambiar estado de ordenes', NOW(), NOW() UNION ALL
    SELECT 'Cancelar Ordenes', 'orders.cancel', 'orders', 'Cancelar ordenes', NOW(), NOW() UNION ALL
    SELECT 'Eliminar Ordenes', 'orders.delete', 'orders', 'Eliminar ordenes', NOW(), NOW() UNION ALL
    SELECT 'Gestionar Crédito Ventas', 'sales.credit', 'sales', 'Gestionar ventas a crédito y cuotas', NOW(), NOW() UNION ALL
    SELECT 'Registrar Pago Cuota', 'sales.pay-installment', 'sales', 'Registrar pagos de cuotas de crédito', NOW(), NOW()
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug = tmp.slug);

-- ─── Asignar permisos al rol Admin (id=2) y Super Admin (id=1) ───
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug IN ('super_admin', 'admin')
AND p.slug IN (
    'transfers.view','transfers.create','transfers.confirm','transfers.cancel','transfers.delete',
    'orders.view','orders.create','orders.edit','orders.change-status','orders.cancel','orders.delete',
    'sales.credit','sales.pay-installment'
)
AND NOT EXISTS (
    SELECT 1 FROM role_permission rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
);

SET FOREIGN_KEY_CHECKS = 1;
