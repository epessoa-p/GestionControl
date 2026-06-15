-- Elimina módulos Caja Chica y Comisiones
-- Ejecutar ANTES de schema_treasury.sql
-- ADVERTENCIA: Esta operación es irreversible. Hacer backup antes de ejecutar.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `petty_cash_movements`;
DROP TABLE IF EXISTS `petty_cashes`;
DROP TABLE IF EXISTS `commissions`;

SET FOREIGN_KEY_CHECKS = 1;
