-- ============================================================
-- Script de limpieza: Eliminar tablas y datos de préstamos
-- Sistema CRM Gestión y Control
-- Fecha: 2026-03-23
-- ============================================================
-- Este script elimina todas las tablas y datos relacionados
-- con la lógica de negocio de préstamos (loans), clientes,
-- categorías de crédito y registros asociados.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------
-- 1. Eliminar tablas de préstamos y relacionadas
-- -----------------------------------------------

-- Tablas de contratos y adjuntos de préstamos
DROP TABLE IF EXISTS `loan_contract_attachments`;
DROP TABLE IF EXISTS `loan_contracts`;

-- Tablas de amortización e intereses
DROP TABLE IF EXISTS `loan_amortizations`;
DROP TABLE IF EXISTS `loan_interest_payments`;

-- Imágenes de préstamos
DROP TABLE IF EXISTS `loan_images`;

-- Garantías de préstamos
DROP TABLE IF EXISTS `loan_collaterals`;

-- Pagos de préstamos
DROP TABLE IF EXISTS `loan_payments`;

-- Préstamos principales
DROP TABLE IF EXISTS `loans`;

-- Tipos de préstamos
DROP TABLE IF EXISTS `loan_types`;

-- Clientes (asociados a préstamos)
DROP TABLE IF EXISTS `clients`;

-- Categorías y reglas de crédito
DROP TABLE IF EXISTS `credit_category_rules`;
DROP TABLE IF EXISTS `credit_categories`;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------
-- 2. Limpiar permisos relacionados a préstamos
-- -----------------------------------------------

-- Eliminar asignaciones de permisos de préstamos en pivots
DELETE rp FROM `role_permission` rp
INNER JOIN `permissions` p ON rp.permission_id = p.id
WHERE p.module IN ('loans', 'payments');

DELETE up FROM `user_permission` up
INNER JOIN `permissions` p ON up.permission_id = p.id
WHERE p.module IN ('loans', 'payments');

-- Eliminar los permisos de préstamos y pagos
DELETE FROM `permissions` WHERE `module` IN ('loans', 'payments');

-- -----------------------------------------------
-- 3. Resumen de tablas eliminadas
-- -----------------------------------------------
-- loan_contract_attachments
-- loan_contracts
-- loan_amortizations
-- loan_interest_payments
-- loan_images
-- loan_collaterals
-- loan_payments
-- loans
-- loan_types
-- clients
-- credit_category_rules
-- credit_categories
--
-- Total: 12 tablas eliminadas
-- -----------------------------------------------
