<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseQuotation;
use App\Models\PurchaseQuotationItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseQuotationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');
        $search    = $request->get('q');

        $base = fn() => PurchaseQuotation::where('company_id', $companyId);

        $quotations = $base()
            ->with(['supplier', 'purchaseRequest', 'createdBy'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('quotation_number', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = collect(array_merge(['todos' => $base()->count()],
            array_fill_keys(PurchaseQuotation::STATUSES, 0),
            $base()->groupBy('status')->selectRaw('status, count(*) as total')
                   ->pluck('total', 'status')->toArray()
        ));

        return view('purchases.quotations.index', compact('quotations', 'status', 'counts', 'search'));
    }

    public function create(Request $request)
    {
        $companyId       = $this->getCompanyId();
        $quotationNumber = PurchaseQuotation::generateQuotationNumber($companyId);
        $suppliers       = Supplier::where('company_id', $companyId)->where('status', 'activo')->orderBy('name')->get();
        $products        = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $purchaseRequests= PurchaseRequest::where('company_id', $companyId)
            ->whereIn('status', ['aprobada', 'en_proceso'])->orderBy('request_number')->get();

        $selectedRequest = null;
        if ($request->has('request_id')) {
            $selectedRequest = PurchaseRequest::with('items.product')
                ->find($request->request_id);
        }

        return view('purchases.quotations.create', compact(
            'quotationNumber', 'suppliers', 'products', 'purchaseRequests', 'selectedRequest'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'quotation_date'      => 'required|date',
            'valid_until'         => 'nullable|date|after_or_equal:quotation_date',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            DB::transaction(function () use ($validated, $companyId) {
                $quotation = PurchaseQuotation::create([
                    'company_id'          => $companyId,
                    'quotation_number'    => PurchaseQuotation::generateQuotationNumber($companyId),
                    'purchase_request_id' => $validated['purchase_request_id'] ?? null,
                    'supplier_id'         => $validated['supplier_id'] ?? null,
                    'quotation_date'      => $validated['quotation_date'],
                    'valid_until'         => $validated['valid_until'] ?? null,
                    'status'              => 'borrador',
                    'notes'               => $validated['notes'] ?? null,
                    'created_by'          => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    if (empty($item['product_id'])) continue;
                    $discount = (float) ($item['discount'] ?? 0);
                    $total    = ((float) $item['quantity'] * (float) $item['unit_price']) - $discount;
                    PurchaseQuotationItem::create([
                        'purchase_quotation_id' => $quotation->id,
                        'product_id'            => $item['product_id'],
                        'quantity'              => $item['quantity'],
                        'unit_price'            => $item['unit_price'],
                        'discount'              => $discount,
                        'total'                 => max(0, $total),
                    ]);
                }

                $quotation->recalculateTotals();
            });

            return redirect()->route('purchases.quotations.index')
                ->with('success', 'Cotización creada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear cotización', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la cotización.');
        }
    }

    public function show(PurchaseQuotation $purchaseQuotation)
    {
        $this->authorizeRecord($purchaseQuotation);
        $purchaseQuotation->load(['supplier', 'purchaseRequest.items.product', 'items.product', 'createdBy', 'purchaseOrders']);
        return view('purchases.quotations.show', ['quotation' => $purchaseQuotation]);
    }

    public function updateStatus(Request $request, PurchaseQuotation $purchaseQuotation)
    {
        $this->authorizeRecord($purchaseQuotation);
        $request->validate(['status' => 'required|in:' . implode(',', PurchaseQuotation::STATUSES)]);
        $purchaseQuotation->update(['status' => $request->status]);
        return back()->with('success', 'Estado actualizado a: ' . PurchaseQuotation::STATUS_LABELS[$request->status]);
    }

    public function approve(PurchaseQuotation $purchaseQuotation)
    {
        $this->authorizeRecord($purchaseQuotation);

        if (!in_array($purchaseQuotation->status, ['recibida', 'enviada', 'borrador'])) {
            return back()->with('error', 'Solo se pueden aprobar cotizaciones recibidas o enviadas.');
        }

        DB::transaction(function () use ($purchaseQuotation) {
            $purchaseQuotation->update(['status' => 'aprobada']);
            if ($purchaseQuotation->purchaseRequest) {
                $purchaseQuotation->purchaseRequest->update(['status' => 'en_proceso']);
            }
        });

        return back()->with('success', 'Cotización aprobada exitosamente.');
    }

    public function destroy(PurchaseQuotation $purchaseQuotation)
    {
        $this->authorizeRecord($purchaseQuotation);
        if (!in_array($purchaseQuotation->status, ['borrador', 'rechazada', 'cancelada'])) {
            return back()->with('error', 'No se puede eliminar esta cotización en su estado actual.');
        }
        $purchaseQuotation->delete();
        return redirect()->route('purchases.quotations.index')->with('success', 'Cotización eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(PurchaseQuotation $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
