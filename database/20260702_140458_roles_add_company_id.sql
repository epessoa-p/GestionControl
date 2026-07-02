-- ════════════════════════════════════════════════════════════════
--  Roles por empresa
--  Agrega company_id a roles. Los roles con company_id = NULL quedan
--  como PLANTILLAS globales (fuente para clonar; hogar del super_admin).
--  Los roles de empresa llevan company_id seteado y el MISMO slug que
--  su plantilla (ej. 'admin') para no romper CheckRole / hasRoleInCompany.
--
--  Tras ejecutar este script, correr:  php artisan roles:make-per-company
-- ════════════════════════════════════════════════════════════════

ALTER TABLE `roles`
    ADD COLUMN `company_id` BIGINT UNSIGNED NULL AFTER `id`,
    ADD CONSTRAINT `fk_roles_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- El slug ya no es único global: cada empresa puede tener su propio 'admin', etc.
ALTER TABLE `roles` DROP INDEX `roles_slug_unique`;
ALTER TABLE `roles` ADD UNIQUE KEY `roles_company_slug` (`company_id`, `slug`);
