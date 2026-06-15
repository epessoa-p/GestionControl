<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Client;
use App\Models\Commission;
use App\Models\Personal;
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
    public function index()
    {
        $companyId = $this->getCompanyId();

        $products = Product::where('company_id', $companyId)
            ->where('active', true)
            ->where('category', 'PRODUCTO FINAL')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'current_stock', 'unit']);

        $session = $this->activeSession($companyId);

        return view('sales.pos.index', [
            'products'    => $products,
            'clients'     => Client::where('company_id', $companyId)->orderBy('name')->get(),
            'promoters'   => Promoter::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'branches'    => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses'  => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'session'     => $session,
            'nextNumber'  => Sale::generateNumber($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'        => 'nullable|exists:clients,id',
            'client_name'      => 'nullable|string|max:255',
            'promoter_id'      => 'nullable|exists:promoters,id',
            'branch_id'        => 'nullable|exists:branches,id',
            'warehouse_id'     => 'nullable|exists:warehouses,id',
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

        try {
            $sale = DB::transaction(function () use ($validated, $companyId) {
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

                $session = $this->activeSession($companyId);

                // Validar stock antes de crear
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->current_stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$product->name}.");
                    }
                }

                $sale = Sale::create([
                    'company_id'      => $companyId,
                    'client_id'       => $clientId,
                    'sale_number'     => Sale::generateNumber($companyId),
                    'sale_date'       => now()->toDateString(),
                    'client_name'     => $clientName,
                    'promoter_id'     => $validated['promoter_id'] ?? null,
                    'branch_id'       => $validated['branch_id'] ?? null,
                    'warehouse_id'    => $validated['warehouse_id'] ?? null,
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
                    if (!empty($validated['warehouse_id'])) {
                        StockService::adjust($companyId, (int) $validated['warehouse_id'], (int) $item['product_id'], -(float) $item['quantity']);
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

    /** Sesión de caja abierta asignada al usuario actual, si existe. */
    private function activeSession(?int $companyId): ?CashSession
    {
        if (!$companyId) {
            return null;
        }
        $personal = Personal::where('user_id', auth()->id())->where('company_id', $companyId)->first();
        if (!$personal) {
            return null;
        }
        $register = CashRegister::where('assigned_personal_id', $personal->id)
            ->where('company_id', $companyId)->where('active', true)->first();
        return $register?->activeSession();
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }
}
