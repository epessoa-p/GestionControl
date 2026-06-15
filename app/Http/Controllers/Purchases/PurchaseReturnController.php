<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseReception;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');

        $base = fn() => PurchaseReturn::where('company_id', $companyId);

        $returns = $base()
            ->with(['supplier', 'createdBy'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'todos'      => $base()->count(),
            'borrador'   => $base()->where('status', 'borrador')->count(),
            'confirmada' => $base()->where('status', 'confirmada')->count(),
            'cancelada'  => $base()->where('status', 'cancelada')->count(),
        ];

        return view('purchases.returns.index', compact('returns', 'status', 'counts'));
    }

    public function create()
    {
        $companyId    = $this->getCompanyId();
        $returnNumber = PurchaseReturn::generateReturnNumber($companyId);
        $suppliers    = Supplier::where('company_id', $companyId)->where('status', 'activo')->orderBy('name')->get();
        $products     = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $receptions   = PurchaseReception::where('company_id', $companyId)
            ->where('status', 'confirmada')
            ->with('purchaseOrder.supplier')
            ->latest()
            ->get();

        return view('purchases.returns.create', compact('returnNumber', 'suppliers', 'products', 'receptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_reception_id' => 'nullable|exists:purchase_receptions,id',
            'supplier_id'           => 'required|exists:suppliers,id',
            'return_date'           => 'required|date',
            'reason'                => 'required|in:' . implode(',', PurchaseReturn::REASONS),
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            DB::transaction(function () use ($validated, $companyId) {
                $return = PurchaseReturn::create([
                    'company_id'            => $companyId,
                    'return_number'         => PurchaseReturn::generateReturnNumber($companyId),
                    'purchase_reception_id' => $validated['purchase_reception_id'] ?? null,
                    'supplier_id'           => $validated['supplier_id'],
                    'return_date'           => $validated['return_date'],
                    'reason'                => $validated['reason'],
                    'status'                => 'borrador',
                    'notes'                 => $validated['notes'] ?? null,
                    'created_by'            => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    if (empty($item['product_id'])) continue;
                    $total = (float) $item['quantity'] * (float) $item['unit_price'];
                    PurchaseReturnItem::create([
                        'purchase_return_id' => $return->id,
                        'product_id'         => $item['product_id'],
                        'quantity'           => $item['quantity'],
                        'unit_price'         => $item['unit_price'],
                        'total'              => $total,
                    ]);
                }
            });

            return redirect()->route('purchases.returns.index')
                ->with('success', 'Devolución creada. Confirma cuando esté lista para enviar.');
        } catch (\Throwable $e) {
            Log::error('Error al crear devolución', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la devolución.');
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeRecord($purchaseReturn);
        $purchaseReturn->load(['supplier', 'reception.purchaseOrder', 'createdBy', 'items.product']);
        return view('purchases.returns.show', compact('purchaseReturn'));
    }

    public function confirm(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeRecord($purchaseReturn);
        if ($purchaseReturn->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden confirmar devoluciones en borrador.');
        }

        try {
            $purchaseReturn->load('items.product');
            $purchaseReturn->confirm();
            return back()->with('success', 'Devolución confirmada. Stock actualizado.');
        } catch (\Throwable $e) {
            Log::error('Error al confirmar devolución', ['message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible confirmar la devolución.');
        }
    }

    public function cancel(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeRecord($purchaseReturn);
        if ($purchaseReturn->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden cancelar devoluciones en borrador.');
        }
        $purchaseReturn->update(['status' => 'cancelada']);
        return back()->with('success', 'Devolución cancelada.');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeRecord($purchaseReturn);
        if ($purchaseReturn->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden eliminar devoluciones en borrador.');
        }
        $purchaseReturn->delete();
        return redirect()->route('purchases.returns.index')->with('success', 'Devolución eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(PurchaseReturn $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
