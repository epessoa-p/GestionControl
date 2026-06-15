<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = SalesReturn::with(['client', 'sale', 'createdBy'])
            ->where('company_id', $companyId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('sales.returns.index', [
            'returns' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('sales.returns.form', [
            'return'     => null,
            'clients'    => Client::where('company_id', $companyId)->orderBy('name')->get(),
            'products'   => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'sales'      => Sale::where('company_id', $companyId)->where('status', 'completed')->latest()->limit(200)->get(),
            'nextNumber' => SalesReturn::generateNumber($companyId),
            'action'     => route('sales-returns.store'),
            'method'     => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id'      => 'nullable|exists:sales,id',
            'client_id'    => 'nullable|exists:clients,id',
            'client_name'  => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'return_date'  => 'required|date',
            'reason'       => 'required|in:defectuoso,incorrecto,cliente,otro',
            'notes'        => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            $return = DB::transaction(function () use ($validated, $companyId) {
                $return = SalesReturn::create([
                    'company_id'    => $companyId,
                    'return_number' => SalesReturn::generateNumber($companyId),
                    'sale_id'       => $validated['sale_id'] ?? null,
                    'client_id'     => $validated['client_id'] ?? null,
                    'client_name'   => $validated['client_name'] ?? null,
                    'warehouse_id'  => $validated['warehouse_id'] ?? null,
                    'return_date'   => $validated['return_date'],
                    'reason'        => $validated['reason'],
                    'status'        => 'borrador',
                    'notes'         => $validated['notes'] ?? null,
                    'created_by'    => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    SalesReturnItem::create([
                        'sales_return_id' => $return->id,
                        'product_id'      => $item['product_id'],
                        'quantity'        => $item['quantity'],
                        'unit_price'      => $item['unit_price'],
                        'total'           => $item['quantity'] * $item['unit_price'],
                    ]);
                }
                $return->recalculateTotal();
                return $return;
            });

            return redirect()->route('sales-returns.show', $return)->with('success', 'Devolución registrada. Confírmala para reingresar el inventario.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear devolución de venta', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la devolución.');
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorizeRecord($salesReturn);
        $salesReturn->load(['items.product', 'client', 'sale', 'warehouse', 'createdBy']);
        return view('sales.returns.show', ['return' => $salesReturn]);
    }

    public function confirm(SalesReturn $salesReturn)
    {
        $this->authorizeRecord($salesReturn);
        if ($salesReturn->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden confirmar devoluciones en borrador.');
        }

        try {
            DB::transaction(function () use ($salesReturn) {
                foreach ($salesReturn->items as $item) {
                    Product::where('id', $item->product_id)->increment('current_stock', $item->quantity);
                    if ($salesReturn->warehouse_id) {
                        StockService::adjust($salesReturn->company_id, $salesReturn->warehouse_id, $item->product_id, (float) $item->quantity);
                    }
                }
                $salesReturn->update(['status' => 'confirmada']);
            });
            return back()->with('success', 'Devolución confirmada. Inventario reingresado.');
        } catch (\Throwable $e) {
            Log::error('Error al confirmar devolución de venta', ['id' => $salesReturn->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible confirmar la devolución.');
        }
    }

    public function cancel(SalesReturn $salesReturn)
    {
        $this->authorizeRecord($salesReturn);
        if ($salesReturn->status === 'cancelada') {
            return back()->with('error', 'La devolución ya está cancelada.');
        }

        try {
            DB::transaction(function () use ($salesReturn) {
                // Si estaba confirmada, revertir el reingreso de stock
                if ($salesReturn->status === 'confirmada') {
                    foreach ($salesReturn->items as $item) {
                        Product::where('id', $item->product_id)->decrement('current_stock', $item->quantity);
                        if ($salesReturn->warehouse_id) {
                            StockService::adjust($salesReturn->company_id, $salesReturn->warehouse_id, $item->product_id, -(float) $item->quantity);
                        }
                    }
                }
                $salesReturn->update(['status' => 'cancelada']);
            });
            return back()->with('success', 'Devolución cancelada.');
        } catch (\Throwable $e) {
            Log::error('Error al cancelar devolución de venta', ['id' => $salesReturn->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible cancelar la devolución.');
        }
    }

    public function destroy(SalesReturn $salesReturn)
    {
        $this->authorizeRecord($salesReturn);
        if ($salesReturn->status === 'confirmada') {
            return back()->with('error', 'No se puede eliminar una devolución confirmada. Cancélala primero.');
        }
        $salesReturn->items()->delete();
        $salesReturn->delete();
        return redirect()->route('sales-returns.index')->with('success', 'Devolución eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord($record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
