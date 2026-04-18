-- ============================================================
-- Script: Permisos faltantes por módulos activos
-- Fecha: 2026-04-18
-- Uso: ejecutar en MySQL (idempotente)
-- ============================================================

START TRANSACTION;

-- 1) Insertar permisos faltantes
INSERT INTO permissions (name, slug, module, description, created_at, updated_at)
SELECT tmp.name, tmp.slug, tmp.module, tmp.description, NOW(), NOW()
FROM (
    -- Branches
    SELECT 'Ver Sucursales' AS name, 'branches.view' AS slug, 'branches' AS module, 'Ver sucursales' AS description
    UNION ALL SELECT 'Crear Sucursales', 'branches.create', 'branches', 'Crear sucursales'
    UNION ALL SELECT 'Editar Sucursales', 'branches.edit', 'branches', 'Editar sucursales'
    UNION ALL SELECT 'Eliminar Sucursales', 'branches.delete', 'branches', 'Eliminar sucursales'

    -- Cargos
    UNION ALL SELECT 'Ver Cargos', 'cargos.view', 'cargos', 'Ver cargos'
    UNION ALL SELECT 'Crear Cargos', 'cargos.create', 'cargos', 'Crear cargos'
    UNION ALL SELECT 'Editar Cargos', 'cargos.edit', 'cargos', 'Editar cargos'
    UNION ALL SELECT 'Eliminar Cargos', 'cargos.delete', 'cargos', 'Eliminar cargos'

    -- Personal
    UNION ALL SELECT 'Ver Personal', 'personal.view', 'personal', 'Ver personal'
    UNION ALL SELECT 'Crear Personal', 'personal.create', 'personal', 'Crear personal'
    UNION ALL SELECT 'Editar Personal', 'personal.edit', 'personal', 'Editar personal'
    UNION ALL SELECT 'Eliminar Personal', 'personal.delete', 'personal', 'Eliminar personal'

    -- Productos
    UNION ALL SELECT 'Ver Productos', 'products.view', 'products', 'Ver productos'
    UNION ALL SELECT 'Crear Productos', 'products.create', 'products', 'Crear productos'
    UNION ALL SELECT 'Editar Productos', 'products.edit', 'products', 'Editar productos'
    UNION ALL SELECT 'Eliminar Productos', 'products.delete', 'products', 'Eliminar productos'

    -- Unidades de medida
    UNION ALL SELECT 'Ver Unidades de Medida', 'measurement-units.view', 'measurement-units', 'Ver unidades de medida'
    UNION ALL SELECT 'Crear Unidades de Medida', 'measurement-units.create', 'measurement-units', 'Crear unidades de medida'
    UNION ALL SELECT 'Editar Unidades de Medida', 'measurement-units.edit', 'measurement-units', 'Editar unidades de medida'
    UNION ALL SELECT 'Eliminar Unidades de Medida', 'measurement-units.delete', 'measurement-units', 'Eliminar unidades de medida'

    -- Almacenes
    UNION ALL SELECT 'Ver Almacenes', 'warehouses.view', 'warehouses', 'Ver almacenes'
    UNION ALL SELECT 'Crear Almacenes', 'warehouses.create', 'warehouses', 'Crear almacenes'
    UNION ALL SELECT 'Editar Almacenes', 'warehouses.edit', 'warehouses', 'Editar almacenes'
    UNION ALL SELECT 'Eliminar Almacenes', 'warehouses.delete', 'warehouses', 'Eliminar almacenes'

    -- Seguimientos
    UNION ALL SELECT 'Ver Seguimientos', 'trackings.view', 'trackings', 'Ver seguimientos'
    UNION ALL SELECT 'Crear Seguimientos', 'trackings.create', 'trackings', 'Crear seguimientos'
    UNION ALL SELECT 'Editar Seguimientos', 'trackings.edit', 'trackings', 'Editar seguimientos'
    UNION ALL SELECT 'Eliminar Seguimientos', 'trackings.delete', 'trackings', 'Eliminar seguimientos'

    -- Entradas
    UNION ALL SELECT 'Ver Entradas', 'entries.view', 'entries', 'Ver entradas'
    UNION ALL SELECT 'Crear Entradas', 'entries.create', 'entries', 'Crear entradas'
    UNION ALL SELECT 'Confirmar Entradas', 'entries.confirm', 'entries', 'Confirmar entradas'
    UNION ALL SELECT 'Cancelar Entradas', 'entries.cancel', 'entries', 'Cancelar entradas'
    UNION ALL SELECT 'Eliminar Entradas', 'entries.delete', 'entries', 'Eliminar entradas'

    -- Salidas
    UNION ALL SELECT 'Ver Salidas', 'departures.view', 'departures', 'Ver salidas'
    UNION ALL SELECT 'Crear Salidas', 'departures.create', 'departures', 'Crear salidas'
    UNION ALL SELECT 'Confirmar Salidas', 'departures.confirm', 'departures', 'Confirmar salidas'
    UNION ALL SELECT 'Cancelar Salidas', 'departures.cancel', 'departures', 'Cancelar salidas'
    UNION ALL SELECT 'Eliminar Salidas', 'departures.delete', 'departures', 'Eliminar salidas'

    -- Cajas
    UNION ALL SELECT 'Ver Cajas', 'cash-registers.view', 'cash-registers', 'Ver cajas'
    UNION ALL SELECT 'Crear Cajas', 'cash-registers.create', 'cash-registers', 'Crear cajas'
    UNION ALL SELECT 'Editar Cajas', 'cash-registers.edit', 'cash-registers', 'Editar cajas'
    UNION ALL SELECT 'Eliminar Cajas', 'cash-registers.delete', 'cash-registers', 'Eliminar cajas'
    UNION ALL SELECT 'Abrir Sesión de Caja', 'cash-registers.open-session', 'cash-registers', 'Abrir sesión de caja'

    -- Sesiones de caja
    UNION ALL SELECT 'Ver Sesiones de Caja', 'cash-sessions.view', 'cash-sessions', 'Ver sesiones de caja'
    UNION ALL SELECT 'Cerrar Sesiones de Caja', 'cash-sessions.close', 'cash-sessions', 'Cerrar sesiones de caja'
    UNION ALL SELECT 'Agregar Movimiento en Sesión', 'cash-sessions.add-movement', 'cash-sessions', 'Agregar movimientos a sesión de caja'

    -- Caja chica
    UNION ALL SELECT 'Ver Caja Chica', 'petty-cash.view', 'petty-cash', 'Ver caja chica'
    UNION ALL SELECT 'Crear Caja Chica', 'petty-cash.create', 'petty-cash', 'Crear caja chica'
    UNION ALL SELECT 'Agregar Movimiento Caja Chica', 'petty-cash.add-movement', 'petty-cash', 'Agregar movimientos de caja chica'
    UNION ALL SELECT 'Eliminar Caja Chica', 'petty-cash.delete', 'petty-cash', 'Eliminar caja chica'

    -- Producción
    UNION ALL SELECT 'Ver Producción', 'productions.view', 'productions', 'Ver producción'
    UNION ALL SELECT 'Crear Producción', 'productions.create', 'productions', 'Crear producción'
    UNION ALL SELECT 'Actualizar Estado Producción', 'productions.update-status', 'productions', 'Actualizar estado de producción'
    UNION ALL SELECT 'Eliminar Producción', 'productions.delete', 'productions', 'Eliminar producción'

    -- Promotores
    UNION ALL SELECT 'Ver Promotores', 'promoters.view', 'promoters', 'Ver promotores'
    UNION ALL SELECT 'Crear Promotores', 'promoters.create', 'promoters', 'Crear promotores'
    UNION ALL SELECT 'Editar Promotores', 'promoters.edit', 'promoters', 'Editar promotores'
    UNION ALL SELECT 'Eliminar Promotores', 'promoters.delete', 'promoters', 'Eliminar promotores'

    -- Ventas
    UNION ALL SELECT 'Ver Ventas', 'sales.view', 'sales', 'Ver ventas'
    UNION ALL SELECT 'Crear Ventas', 'sales.create', 'sales', 'Crear ventas'
    UNION ALL SELECT 'Completar Ventas', 'sales.complete', 'sales', 'Completar ventas'
    UNION ALL SELECT 'Cancelar Ventas', 'sales.cancel', 'sales', 'Cancelar ventas'
    UNION ALL SELECT 'Eliminar Ventas', 'sales.delete', 'sales', 'Eliminar ventas'

    -- Traspasos (complemento)
    UNION ALL SELECT 'Despachar Traspasos', 'transfers.dispatch', 'transfers', 'Despachar traspasos'
    UNION ALL SELECT 'Completar Traspasos', 'transfers.complete', 'transfers', 'Completar traspasos'

    -- Ordenes (complemento)
    UNION ALL SELECT 'Actualizar Estado Ordenes', 'orders.update-status', 'orders', 'Actualizar estado de ordenes'

    -- Comisiones
    UNION ALL SELECT 'Ver Comisiones', 'commissions.view', 'commissions', 'Ver comisiones'
    UNION ALL SELECT 'Marcar Comisiones Pagadas', 'commissions.mark-paid', 'commissions', 'Marcar comisiones pagadas'
    UNION ALL SELECT 'Marcar Comisiones Pagadas Masivo', 'commissions.mark-paid-bulk', 'commissions', 'Marcar comisiones pagadas en lote'
    UNION ALL SELECT 'Eliminar Comisiones', 'commissions.delete', 'commissions', 'Eliminar comisiones'

    -- Reportes
    UNION ALL SELECT 'Ver Módulo Reportes', 'reports.view-module', 'reports', 'Ver módulo de reportes'
    UNION ALL SELECT 'Ver Reporte de Ventas', 'reports.sales', 'reports', 'Ver reporte de ventas'
    UNION ALL SELECT 'Ver Reporte de Inventario', 'reports.inventory', 'reports', 'Ver reporte de inventario'
    UNION ALL SELECT 'Ver Reporte de Comisiones', 'reports.commissions', 'reports', 'Ver reporte de comisiones'
    UNION ALL SELECT 'Ver Reporte de Movimientos de Caja', 'reports.cash-movements', 'reports', 'Ver reporte de movimientos de caja'
    UNION ALL SELECT 'Ver Reporte de Producción', 'reports.production', 'reports', 'Ver reporte de producción'

    -- Plantillas
    UNION ALL SELECT 'Ver Plantillas', 'document-templates.view', 'document-templates', 'Ver plantillas de documentos'
    UNION ALL SELECT 'Crear Plantillas', 'document-templates.create', 'document-templates', 'Crear plantillas de documentos'
    UNION ALL SELECT 'Editar Plantillas', 'document-templates.edit', 'document-templates', 'Editar plantillas de documentos'
    UNION ALL SELECT 'Eliminar Plantillas', 'document-templates.delete', 'document-templates', 'Eliminar plantillas de documentos'
    UNION ALL SELECT 'Descargar Plantilla Word', 'document-templates.download-word', 'document-templates', 'Descargar plantilla en Word'
    UNION ALL SELECT 'Exportar Plantilla PDF', 'document-templates.export-pdf', 'document-templates', 'Exportar plantilla en PDF'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM permissions p WHERE p.slug = tmp.slug
);

-- 2) Asignar permisos faltantes a roles admin y super_admin
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON 1=1
JOIN (
  SELECT 'branches.view' AS slug
  UNION ALL SELECT 'branches.create'
  UNION ALL SELECT 'branches.edit'
  UNION ALL SELECT 'branches.delete'
  UNION ALL SELECT 'cargos.view'
  UNION ALL SELECT 'cargos.create'
  UNION ALL SELECT 'cargos.edit'
  UNION ALL SELECT 'cargos.delete'
  UNION ALL SELECT 'personal.view'
  UNION ALL SELECT 'personal.create'
  UNION ALL SELECT 'personal.edit'
  UNION ALL SELECT 'personal.delete'
  UNION ALL SELECT 'products.view'
  UNION ALL SELECT 'products.create'
  UNION ALL SELECT 'products.edit'
  UNION ALL SELECT 'products.delete'
  UNION ALL SELECT 'measurement-units.view'
  UNION ALL SELECT 'measurement-units.create'
  UNION ALL SELECT 'measurement-units.edit'
  UNION ALL SELECT 'measurement-units.delete'
  UNION ALL SELECT 'warehouses.view'
  UNION ALL SELECT 'warehouses.create'
  UNION ALL SELECT 'warehouses.edit'
  UNION ALL SELECT 'warehouses.delete'
  UNION ALL SELECT 'trackings.view'
  UNION ALL SELECT 'trackings.create'
  UNION ALL SELECT 'trackings.edit'
  UNION ALL SELECT 'trackings.delete'
  UNION ALL SELECT 'entries.view'
  UNION ALL SELECT 'entries.create'
  UNION ALL SELECT 'entries.confirm'
  UNION ALL SELECT 'entries.cancel'
  UNION ALL SELECT 'entries.delete'
  UNION ALL SELECT 'departures.view'
  UNION ALL SELECT 'departures.create'
  UNION ALL SELECT 'departures.confirm'
  UNION ALL SELECT 'departures.cancel'
  UNION ALL SELECT 'departures.delete'
  UNION ALL SELECT 'cash-registers.view'
  UNION ALL SELECT 'cash-registers.create'
  UNION ALL SELECT 'cash-registers.edit'
  UNION ALL SELECT 'cash-registers.delete'
  UNION ALL SELECT 'cash-registers.open-session'
  UNION ALL SELECT 'cash-sessions.view'
  UNION ALL SELECT 'cash-sessions.close'
  UNION ALL SELECT 'cash-sessions.add-movement'
  UNION ALL SELECT 'petty-cash.view'
  UNION ALL SELECT 'petty-cash.create'
  UNION ALL SELECT 'petty-cash.add-movement'
  UNION ALL SELECT 'petty-cash.delete'
  UNION ALL SELECT 'productions.view'
  UNION ALL SELECT 'productions.create'
  UNION ALL SELECT 'productions.update-status'
  UNION ALL SELECT 'productions.delete'
  UNION ALL SELECT 'promoters.view'
  UNION ALL SELECT 'promoters.create'
  UNION ALL SELECT 'promoters.edit'
  UNION ALL SELECT 'promoters.delete'
  UNION ALL SELECT 'sales.view'
  UNION ALL SELECT 'sales.create'
  UNION ALL SELECT 'sales.complete'
  UNION ALL SELECT 'sales.cancel'
  UNION ALL SELECT 'sales.delete'
  UNION ALL SELECT 'transfers.dispatch'
  UNION ALL SELECT 'transfers.complete'
  UNION ALL SELECT 'orders.update-status'
  UNION ALL SELECT 'commissions.view'
  UNION ALL SELECT 'commissions.mark-paid'
  UNION ALL SELECT 'commissions.mark-paid-bulk'
  UNION ALL SELECT 'commissions.delete'
  UNION ALL SELECT 'reports.view-module'
  UNION ALL SELECT 'reports.sales'
  UNION ALL SELECT 'reports.inventory'
  UNION ALL SELECT 'reports.commissions'
  UNION ALL SELECT 'reports.cash-movements'
  UNION ALL SELECT 'reports.production'
  UNION ALL SELECT 'document-templates.view'
  UNION ALL SELECT 'document-templates.create'
  UNION ALL SELECT 'document-templates.edit'
  UNION ALL SELECT 'document-templates.delete'
  UNION ALL SELECT 'document-templates.download-word'
  UNION ALL SELECT 'document-templates.export-pdf'
) target ON target.slug = p.slug
WHERE r.slug IN ('super_admin', 'admin')
  AND NOT EXISTS (
      SELECT 1
      FROM role_permission rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );

COMMIT;
