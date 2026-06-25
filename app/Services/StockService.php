<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Devuelve [product_id => stock] para un almacén concreto.
     * Para productos que aún no tienen registro por almacén (sin backfill todavía)
     * se usa como respaldo el current_stock global, para no romper la operación.
     *
     * @param  Collection  $products  Colección de modelos Product (deben traer current_stock).
     */
    public static function warehouseStocks(int $warehouseId, Collection $products): array
    {
        $ids = $products->pluck('id');
        if ($ids->isEmpty()) {
            return [];
        }

        // Productos que YA tienen seguimiento por almacén (en cualquier almacén)
        $tracked = DB::table('warehouse_product_stocks')
            ->whereIn('product_id', $ids)
            ->pluck('product_id')
            ->flip();

        // Stock de ESTE almacén
        $here = DB::table('warehouse_product_stocks')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $ids)
            ->pluck('quantity', 'product_id');

        $map = [];
        foreach ($products as $p) {
            if ($tracked->has($p->id)) {
                $map[$p->id] = (float) ($here[$p->id] ?? 0);
            } else {
                // Sin datos por almacén → asumir el global mientras no haya backfill.
                $map[$p->id] = (float) $p->current_stock;
            }
        }
        return $map;
    }

    public static function adjust(int $companyId, int $warehouseId, int $productId, float $delta): void
    {
        if ($delta == 0) {
            return;
        }

        DB::table('warehouse_product_stocks')->insertOrIgnore([
            'company_id'   => $companyId,
            'warehouse_id' => $warehouseId,
            'product_id'   => $productId,
            'quantity'     => 0,
            'updated_at'   => now(),
        ]);

        if ($delta > 0) {
            DB::table('warehouse_product_stocks')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->increment('quantity', $delta, ['updated_at' => now()]);
        } else {
            DB::table('warehouse_product_stocks')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->decrement('quantity', abs($delta), ['updated_at' => now()]);
        }
    }
}
