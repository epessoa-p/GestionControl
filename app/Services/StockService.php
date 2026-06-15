<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StockService
{
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
