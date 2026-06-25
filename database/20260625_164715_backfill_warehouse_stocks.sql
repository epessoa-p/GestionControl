-- ════════════════════════════════════════════════════════════════
--  Backfill de stock por almacén (warehouse_product_stocks)
--  Recalcula el stock actual de cada (almacén, producto) a partir de
--  TODOS los documentos confirmados/completados. Es idempotente:
--  se puede volver a ejecutar y deja los saldos consistentes.
--
--  Necesario para que el POS y "Nueva venta" muestren el stock real
--  del almacén ligado a la sucursal de la caja del cajero.
-- ════════════════════════════════════════════════════════════════

INSERT INTO `warehouse_product_stocks` (`company_id`, `warehouse_id`, `product_id`, `quantity`, `updated_at`)
SELECT s.company_id, s.warehouse_id, s.product_id, SUM(s.qty) AS quantity, NOW()
FROM (
    -- Entradas confirmadas (+)
    SELECT e.company_id, e.warehouse_id, ed.product_id, ed.quantity AS qty
    FROM entry_details ed
    JOIN entries e ON e.id = ed.entry_id
    WHERE e.status = 'confirmed' AND e.deleted_at IS NULL AND ed.deleted_at IS NULL

    UNION ALL
    -- Salidas confirmadas (-)
    SELECT d.company_id, d.warehouse_id, dd.product_id, -dd.quantity
    FROM departure_details dd
    JOIN departures d ON d.id = dd.departure_id
    WHERE d.status = 'confirmed' AND d.deleted_at IS NULL AND dd.deleted_at IS NULL

    UNION ALL
    -- Traspaso completado: entra al destino (+)
    SELECT wt.company_id, wt.to_warehouse_id, wtd.product_id, wtd.quantity
    FROM warehouse_transfer_details wtd
    JOIN warehouse_transfers wt ON wt.id = wtd.warehouse_transfer_id
    WHERE wt.status = 'completed' AND wt.deleted_at IS NULL AND wtd.deleted_at IS NULL

    UNION ALL
    -- Traspaso completado: sale del origen (-)
    SELECT wt.company_id, wt.from_warehouse_id, wtd.product_id, -wtd.quantity
    FROM warehouse_transfer_details wtd
    JOIN warehouse_transfers wt ON wt.id = wtd.warehouse_transfer_id
    WHERE wt.status = 'completed' AND wt.deleted_at IS NULL AND wtd.deleted_at IS NULL

    UNION ALL
    -- Recepción de compra confirmada (+)
    SELECT pr.company_id, pr.warehouse_id, pri.product_id, pri.quantity_received
    FROM purchase_reception_items pri
    JOIN purchase_receptions pr ON pr.id = pri.purchase_reception_id
    WHERE pr.status = 'confirmada' AND pr.deleted_at IS NULL

    UNION ALL
    -- Venta completada (-)
    SELECT sa.company_id, sa.warehouse_id, sd.product_id, -sd.quantity
    FROM sale_details sd
    JOIN sales sa ON sa.id = sd.sale_id
    WHERE sa.status = 'completed' AND sa.warehouse_id IS NOT NULL
      AND sa.deleted_at IS NULL AND sd.deleted_at IS NULL

    UNION ALL
    -- Producción terminada: producto final (+)
    SELECT p.company_id, p.warehouse_id, p.product_id, p.quantity_produced
    FROM productions p
    WHERE p.status = 'completed' AND p.warehouse_id IS NOT NULL AND p.deleted_at IS NULL

    UNION ALL
    -- Producción: consumo de materia prima (-)
    SELECT p.company_id, p.warehouse_id, pm.product_id, -pm.quantity_used
    FROM production_materials pm
    JOIN productions p ON p.id = pm.production_id
    WHERE p.status = 'completed' AND p.warehouse_id IS NOT NULL
      AND p.deleted_at IS NULL AND pm.deleted_at IS NULL
) s
WHERE s.warehouse_id IS NOT NULL
GROUP BY s.company_id, s.warehouse_id, s.product_id
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`), `updated_at` = NOW();
