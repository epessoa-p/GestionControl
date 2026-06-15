<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    /**
     * Ledger de inventario de solo lectura. Consolida todas las entradas, salidas
     * y traspasos confirmados de la empresa mediante un UNION de varias fuentes.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        $direction   = $request->input('direction');   // entrada | salida | traspaso
        $source      = $request->input('source');       // manual | compra | produccion | venta | traspaso
        $warehouseId = $request->input('warehouse_id');
        $search      = $request->input('q');
        $from        = $request->input('from');
        $to          = $request->input('to');

        // ── Subconsultas normalizadas ───────────────────────────────
        // Columnas comunes: movement_date, direction, source, reference_number,
        // reference_type, reference_id, product_id, quantity, warehouse_id, to_warehouse_id, created_by

        $entradaManual = DB::table('entry_details as ed')
            ->join('entries as e', 'e.id', '=', 'ed.entry_id')
            ->where('e.company_id', $companyId)
            ->where('e.status', 'confirmed')
            ->whereNull('e.deleted_at')
            ->whereNull('ed.deleted_at')
            ->selectRaw("e.entry_date as movement_date, 'entrada' as direction, 'manual' as source,
                e.entry_number as reference_number, 'entry' as reference_type, e.id as reference_id,
                ed.product_id, ed.quantity as quantity, e.warehouse_id, NULL as to_warehouse_id, e.created_by");

        $entradaCompra = DB::table('purchase_reception_items as pri')
            ->join('purchase_receptions as pr', 'pr.id', '=', 'pri.purchase_reception_id')
            ->where('pr.company_id', $companyId)
            ->where('pr.status', 'confirmada')
            ->whereNull('pr.deleted_at')
            ->selectRaw("pr.reception_date as movement_date, 'entrada' as direction, 'compra' as source,
                pr.reception_number as reference_number, 'reception' as reference_type, pr.id as reference_id,
                pri.product_id, pri.quantity_received as quantity, pr.warehouse_id, NULL as to_warehouse_id, pr.created_by");

        $entradaProduccion = DB::table('productions as p')
            ->where('p.company_id', $companyId)
            ->where('p.status', 'completed')
            ->whereNull('p.deleted_at')
            ->whereNotNull('p.warehouse_id')
            ->selectRaw("p.production_date as movement_date, 'entrada' as direction, 'produccion' as source,
                p.batch_number as reference_number, 'production' as reference_type, p.id as reference_id,
                p.product_id, p.quantity_produced as quantity, p.warehouse_id, NULL as to_warehouse_id, p.created_by");

        $salidaManual = DB::table('departure_details as dd')
            ->join('departures as d', 'd.id', '=', 'dd.departure_id')
            ->where('d.company_id', $companyId)
            ->where('d.status', 'confirmed')
            ->whereNull('d.deleted_at')
            ->whereNull('dd.deleted_at')
            ->selectRaw("d.departure_date as movement_date, 'salida' as direction, 'manual' as source,
                d.departure_number as reference_number, 'departure' as reference_type, d.id as reference_id,
                dd.product_id, dd.quantity as quantity, d.warehouse_id, NULL as to_warehouse_id, d.created_by");

        $salidaVenta = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->where('s.company_id', $companyId)
            ->where('s.status', 'completed')
            ->whereNull('s.deleted_at')
            ->whereNull('sd.deleted_at')
            ->whereNotNull('s.warehouse_id')
            ->selectRaw("s.sale_date as movement_date, 'salida' as direction, 'venta' as source,
                s.sale_number as reference_number, 'sale' as reference_type, s.id as reference_id,
                sd.product_id, sd.quantity as quantity, s.warehouse_id, NULL as to_warehouse_id, s.created_by");

        $salidaProduccion = DB::table('production_materials as pm')
            ->join('productions as p2', 'p2.id', '=', 'pm.production_id')
            ->where('p2.company_id', $companyId)
            ->where('p2.status', 'completed')
            ->whereNull('p2.deleted_at')
            ->whereNull('pm.deleted_at')
            ->whereNotNull('p2.warehouse_id')
            ->selectRaw("p2.production_date as movement_date, 'salida' as direction, 'produccion' as source,
                p2.batch_number as reference_number, 'production' as reference_type, p2.id as reference_id,
                pm.product_id, pm.quantity_used as quantity, p2.warehouse_id, NULL as to_warehouse_id, p2.created_by");

        $traspaso = DB::table('warehouse_transfer_details as wtd')
            ->join('warehouse_transfers as wt', 'wt.id', '=', 'wtd.warehouse_transfer_id')
            ->where('wt.company_id', $companyId)
            ->where('wt.status', 'completed')
            ->whereNull('wt.deleted_at')
            ->whereNull('wtd.deleted_at')
            ->selectRaw("wt.transfer_date as movement_date, 'traspaso' as direction, 'traspaso' as source,
                wt.transfer_number as reference_number, 'transfer' as reference_type, wt.id as reference_id,
                wtd.product_id, wtd.quantity as quantity, wt.from_warehouse_id as warehouse_id, wt.to_warehouse_id, wt.created_by");

        // ── Unir todas las fuentes ──────────────────────────────────
        $union = $entradaManual
            ->unionAll($entradaCompra)
            ->unionAll($entradaProduccion)
            ->unionAll($salidaManual)
            ->unionAll($salidaVenta)
            ->unionAll($salidaProduccion)
            ->unionAll($traspaso);

        // ── Envolver y filtrar ──────────────────────────────────────
        $query = DB::query()->fromSub($union, 'm');

        if ($direction) {
            $query->where('m.direction', $direction);
        }
        if ($source) {
            $query->where('m.source', $source);
        }
        if ($warehouseId) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('m.warehouse_id', $warehouseId)
                  ->orWhere('m.to_warehouse_id', $warehouseId);
            });
        }
        if ($from) {
            $query->whereDate('m.movement_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('m.movement_date', '<=', $to);
        }
        if ($search) {
            $ids = Product::where('company_id', $companyId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                })->pluck('id');
            $query->whereIn('m.product_id', $ids);
        }

        // KPIs del filtro actual
        $countQuery   = clone $query;
        $counts       = $countQuery->selectRaw('m.direction, COUNT(*) as c')->groupBy('m.direction')->pluck('c', 'direction');
        $kpiEntradas  = (int) ($counts['entrada'] ?? 0);
        $kpiSalidas   = (int) ($counts['salida'] ?? 0);
        $kpiTraspasos = (int) ($counts['traspaso'] ?? 0);

        $movements = $query->orderByDesc('m.movement_date')
            ->orderByDesc('m.reference_id')
            ->paginate(25)
            ->withQueryString();

        // Resolver nombres para la página actual
        $productIds   = collect($movements->items())->pluck('product_id')->unique()->filter();
        $warehouseIds = collect($movements->items())
            ->flatMap(fn($r) => [$r->warehouse_id, $r->to_warehouse_id])
            ->unique()->filter();
        $userIds      = collect($movements->items())->pluck('created_by')->unique()->filter();

        $products   = Product::whereIn('id', $productIds)->get(['id', 'name', 'sku'])->keyBy('id');
        $warehouses = Warehouse::whereIn('id', $warehouseIds)->get(['id', 'name'])->keyBy('id');
        $users      = \App\Models\User::whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        $allWarehouses = Warehouse::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-movements.index', compact(
            'movements', 'products', 'warehouses', 'users', 'allWarehouses',
            'direction', 'source', 'warehouseId', 'search', 'from', 'to',
            'kpiEntradas', 'kpiSalidas', 'kpiTraspasos'
        ));
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }
}
