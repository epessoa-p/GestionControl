<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemResetController extends Controller
{
    /**
     * Tablas/datos que se CONSERVAN: branches, warehouses, personals, clients,
     * suppliers, measurement_units, cash_registers, treasury_accounts, products
     * (stock reseteado a 0), y todo lo de sistema (usuarios, roles, permisos, cargos).
     */
    public function index()
    {
        $companies = Company::orderBy('name')->get();
        $current   = $this->currentCompany();
        return view('admin.system-reset.index', compact('companies', 'current'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'confirm_name' => 'required|string',
            'understand'   => 'accepted',
        ], [
            'company_id.required' => 'Selecciona la empresa a reiniciar.',
            'understand.accepted' => 'Debes confirmar que entiendes que la acción es irreversible.',
        ]);

        $company = Company::find($request->company_id);
        if (!$company) {
            return back()->with('error', 'Empresa no encontrada. No se eliminó nada.');
        }

        if (trim($request->confirm_name) !== $company->name) {
            return back()->with('error', 'El nombre escrito no coincide con la empresa seleccionada. No se eliminó nada.');
        }

        $cid = $company->id;

        try {
            DB::transaction(function () use ($cid) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                try {
                    // ── Ventas ──
                    $this->deleteByParent('sale_installments', 'sale_id', 'sales', $cid);
                    $this->deleteByParent('sale_details', 'sale_id', 'sales', $cid);
                    DB::table('sales')->where('company_id', $cid)->delete();

                    // ── Pedidos / Órdenes (operaciones) ──
                    $this->deleteByParent('order_details', 'order_id', 'orders', $cid);
                    DB::table('orders')->where('company_id', $cid)->delete();

                    // ── Entradas / Salidas / Traspasos ──
                    $this->deleteByParent('entry_details', 'entry_id', 'entries', $cid);
                    DB::table('entries')->where('company_id', $cid)->delete();

                    $this->deleteByParent('departure_details', 'departure_id', 'departures', $cid);
                    DB::table('departures')->where('company_id', $cid)->delete();

                    $this->deleteByParent('warehouse_transfer_details', 'warehouse_transfer_id', 'warehouse_transfers', $cid);
                    DB::table('warehouse_transfers')->where('company_id', $cid)->delete();

                    // ── Producción (+ overhead asignado) ──
                    DB::table('overhead_allocations')->whereIn('production_id', fn($q) =>
                        $q->select('id')->from('productions')->where('company_id', $cid))->delete();
                    $this->deleteByParent('production_costs', 'production_id', 'productions', $cid);
                    $this->deleteByParent('production_materials', 'production_id', 'productions', $cid);
                    DB::table('productions')->where('company_id', $cid)->delete();

                    // ── Overhead (períodos) ──
                    $this->deleteByParent('overhead_items', 'overhead_period_id', 'overhead_periods', $cid);
                    DB::table('overhead_allocations')->whereIn('overhead_period_id', fn($q) =>
                        $q->select('id')->from('overhead_periods')->where('company_id', $cid))->delete();
                    DB::table('overhead_periods')->where('company_id', $cid)->delete();

                    // ── Cajas: sesiones y movimientos (se conservan las cajas) ──
                    DB::table('cash_movements')->whereIn('cash_session_id', function ($q) use ($cid) {
                        $q->select('cs.id')->from('cash_sessions as cs')
                          ->join('cash_registers as cr', 'cr.id', '=', 'cs.cash_register_id')
                          ->where('cr.company_id', $cid);
                    })->delete();
                    DB::table('cash_sessions')->whereIn('cash_register_id', fn($q) =>
                        $q->select('id')->from('cash_registers')->where('company_id', $cid))->delete();

                    // ── Tesorería: movimientos (se conservan las cuentas; saldo vuelve al inicial) ──
                    DB::table('treasury_movements')->where('company_id', $cid)->delete();
                    DB::table('treasury_accounts')->where('company_id', $cid)
                        ->update(['current_balance' => DB::raw('initial_balance')]);

                    // ── Cuentas por pagar ──
                    $this->deleteByParent('accounts_payable_payments', 'accounts_payable_id', 'accounts_payable', $cid);
                    DB::table('accounts_payable')->where('company_id', $cid)->delete();

                    // ── Compras ──
                    $this->deleteByParent('purchase_return_items', 'purchase_return_id', 'purchase_returns', $cid);
                    DB::table('purchase_returns')->where('company_id', $cid)->delete();

                    $this->deleteByParent('purchase_reception_items', 'purchase_reception_id', 'purchase_receptions', $cid);
                    DB::table('purchase_receptions')->where('company_id', $cid)->delete();

                    $this->deleteByParent('purchase_order_items', 'purchase_order_id', 'purchase_orders', $cid);
                    DB::table('purchase_orders')->where('company_id', $cid)->delete();

                    $this->deleteByParent('purchase_quotation_items', 'purchase_quotation_id', 'purchase_quotations', $cid);
                    DB::table('purchase_quotations')->where('company_id', $cid)->delete();

                    $this->deleteByParent('purchase_request_items', 'purchase_request_id', 'purchase_requests', $cid);
                    DB::table('purchase_requests')->where('company_id', $cid)->delete();

                    // ── Stock por almacén / ledger ──
                    DB::table('warehouse_product_stocks')->where('company_id', $cid)->delete();
                    DB::table('inventory_movements')->where('company_id', $cid)->delete();

                    // ── Catálogos a eliminar (Recetas, Maquinaria, Promotores, Plantillas) ──
                    $this->deleteByParent('recipe_items', 'recipe_id', 'recipes', $cid);
                    DB::table('recipes')->where('company_id', $cid)->delete();
                    DB::table('machinery')->where('company_id', $cid)->delete();
                    DB::table('promoters')->where('company_id', $cid)->delete();
                    DB::table('document_templates')->where('company_id', $cid)->delete();

                    // ── Productos: se conservan, stock global a 0 ──
                    DB::table('products')->where('company_id', $cid)->update(['current_stock' => 0]);
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            });

            Log::warning('Reinicio de datos ejecutado', ['company_id' => $cid, 'user_id' => auth()->id()]);

            return redirect()->route('dashboard')
                ->with('success', "Datos de «{$company->name}» reiniciados. Se conservaron sucursales, almacenes, personal, clientes, proveedores, unidades, cajas, tesorería y productos (stock en 0).");
        } catch (\Throwable $e) {
            Log::error('Error en reinicio de datos', ['company_id' => $cid, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->with('error', 'No fue posible completar el reinicio: ' . $e->getMessage());
        }
    }

    /** Borra filas de una tabla hija cuyo padre pertenece a la empresa. */
    private function deleteByParent(string $childTable, string $fk, string $parentTable, int $companyId): void
    {
        DB::table($childTable)->whereIn($fk, fn($q) =>
            $q->select('id')->from($parentTable)->where('company_id', $companyId)
        )->delete();
    }

    private function currentCompany(): ?Company
    {
        $user = auth()->user();
        $id = $user->getCurrentCompany()?->id ?? session('current_company_id');
        return $id ? Company::find($id) : null;
    }
}
