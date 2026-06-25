<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\InteractsWithCashSession;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\Client;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Promoter;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    use InteractsWithCashSession;

    public function index()
    {
        $companyId = $this->getCompanyId();

        $assignedRegister = $this->userCashRegister($companyId);
        $session          = $assignedRegister?->activeSession();
        $session?->load('cashRegister.branch.warehouse');

        $branch    = $session?->cashRegister?->branch;
        $warehouse = $branch?->warehouse;

        $products = Product::where('company_id', $companyId)
            ->where('active', true)
            ->where('category', 'PRODUCTO FINAL')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'current_stock', 'unit']);

        // Stock del almacén ligado a la sucursal de la caja.
        $whStocks = $warehouse ? StockService::warehouseStocks($warehouse->id, $products) : [];
        $products->each(fn($p) => $p->wh_stock = $warehouse ? (float) ($whStocks[$p->id] ?? 0) : (float) $p->current_stock);

        return view('sales.pos.index', [
            'products'         => $products,
            'clients'          => Client::where('company_id', $companyId)->orderBy('name')->get(),
            'promoters'        => Promoter::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'session'          => $session,
            'assignedRegister' => $assignedRegister,
            'branch'           => $branch,
            'warehouse'        => $warehouse,
            'nextNumber'       => Sale::generateNumber($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'        => 'nullable|exists:clients,id',
            'client_name'      => 'nullable|string|max:255',
            'promoter_id'      => 'nullable|exists:promoters,id',
            'payment_method'   => 'required|in:cash,card,transfer,other',
            'discount'         => 'nullable|numeric|min:0',
            'tax'              => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        // El POS exige una caja abierta del usuario logueado.
        $session = $this->userOpenSession($companyId);
        if (!$session) {
            return back()->withInput()->with('error', 'Debes abrir tu caja antes de registrar una venta en el POS.');
        }

        // Sucursal y almacén se obtienen de la caja del cajero (no del formulario).
        $session->load('cashRegister.branch');
        $branchId    = $session->cashRegister?->branch_id;
        $warehouseId = $session->cashRegister?->branch?->warehouse_id;

        try {
            $sale = DB::transaction(function () use ($validated, $companyId, $session, $branchId, $warehouseId) {
                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $subtotal += $item['quantity'] * $item['unit_price'];
                }
                $tax      = $validated['tax'] ?? 0;
                $discount = $validated['discount'] ?? 0;
                $total    = $subtotal + $tax - $discount;

                $clientId   = $validated['client_id'] ?? null;
                $clientName = $validated['client_name'] ?? null;
                if ($clientId && !$clientName) {
                    $clientName = Client::find($clientId)?->display_name;
                }

                // Validar stock contra el almacén de la caja
                $itemProducts = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))->get();
                $whStocks = $warehouseId ? StockService::warehouseStocks($warehouseId, $itemProducts) : [];
                foreach ($validated['items'] as $item) {
                    $product = $itemProducts->firstWhere('id', (int) $item['product_id']);
                    $available = $warehouseId ? (float) ($whStocks[$item['product_id']] ?? 0) : (float) ($product?->current_stock ?? 0);
                    if ($product && $available < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$product->name} en el almacén de la caja (disponible: {$available}).");
                    }
                }

                $sale = Sale::create([
                    'company_id'      => $companyId,
                    'client_id'       => $clientId,
                    'sale_number'     => Sale::generateNumber($companyId),
                    'sale_date'       => now()->toDateString(),
                    'client_name'     => $clientName,
                    'promoter_id'     => $validated['promoter_id'] ?? null,
                    'branch_id'       => $branchId,
                    'warehouse_id'    => $warehouseId,
                    'cash_session_id' => $session?->id,
                    'payment_method'  => $validated['payment_method'],
                    'sale_type'       => 'cash',
                    'subtotal'        => $subtotal,
                    'tax'             => $tax,
                    'discount'        => $discount,
                    'total'           => $total,
                    'status'          => 'completed',
                    'notes'           => $validated['notes'] ?? null,
                    'created_by'      => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    SaleDetail::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount'   => 0,
                        'total'      => $item['quantity'] * $item['unit_price'],
                    ]);

                    Product::where('id', $item['product_id'])->decrement('current_stock', $item['quantity']);
                    if ($warehouseId) {
                        StockService::adjust($companyId, (int) $warehouseId, (int) $item['product_id'], -(float) $item['quantity']);
                    }
                }

                // Comisión automática
                if ($sale->promoter_id) {
                    $promoter = Promoter::find($sale->promoter_id);
                    if ($promoter && $promoter->commission_rate > 0) {
                        Commission::create([
                            'company_id'  => $companyId,
                            'promoter_id' => $promoter->id,
                            'sale_id'     => $sale->id,
                            'amount'      => $total * ($promoter->commission_rate / 100),
                            'rate'        => $promoter->commission_rate,
                            'status'      => 'pending',
                            'created_by'  => auth()->id(),
                        ]);
                    }
                }

                // Registrar ingreso en caja si hay sesión abierta y el pago es en efectivo
                if ($session && $validated['payment_method'] === 'cash') {
                    CashMovement::create([
                        'cash_session_id' => $session->id,
                        'type'            => 'income',
                        'category'        => 'venta',
                        'amount'          => $total,
                        'concept'         => 'Venta POS ' . $sale->sale_number,
                        'payment_method'  => 'cash',
                        'reference'       => $sale->sale_number,
                        'movement_date'   => now(),
                        'created_by'      => auth()->id(),
                    ]);
                }

                return $sale;
            });

            return redirect()->route('sales.show', $sale)->with('success', 'Venta POS registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error en POS', ['message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }
}
