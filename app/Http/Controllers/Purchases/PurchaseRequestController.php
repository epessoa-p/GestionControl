<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');
        $search    = $request->get('q');

        $base = fn() => PurchaseRequest::where('company_id', $companyId);

        $requests = $base()
            ->with(['requestedBy', 'createdBy'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('request_number', 'like', "%{$search}%")
                   ->orWhere('department', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = collect(array_merge(['todos' => $base()->count()],
            array_fill_keys(PurchaseRequest::STATUSES, 0),
            $base()->groupBy('status')->selectRaw('status, count(*) as total')
                   ->pluck('total', 'status')->toArray()
        ));

        return view('purchases.requests.index', compact('requests', 'status', 'counts', 'search'));
    }

    public function create()
    {
        $companyId     = $this->getCompanyId();
        $requestNumber = PurchaseRequest::generateRequestNumber($companyId);
        $products      = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $users         = User::whereHas('companies', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();

        return view('purchases.requests.create', compact('requestNumber', 'products', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requested_by' => 'required|exists:users,id',
            'department'   => 'nullable|string|max:100',
            'priority'     => 'required|in:' . implode(',', PurchaseRequest::PRIORITIES),
            'expected_date'=> 'nullable|date',
            'notes'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.estimated_unit_cost'=> 'nullable|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            DB::transaction(function () use ($validated, $companyId, $request) {
                $pr = PurchaseRequest::create([
                    'company_id'    => $companyId,
                    'request_number'=> PurchaseRequest::generateRequestNumber($companyId),
                    'requested_by'  => $validated['requested_by'],
                    'department'    => $validated['department'] ?? null,
                    'priority'      => $validated['priority'],
                    'expected_date' => $validated['expected_date'] ?? null,
                    'status'        => 'borrador',
                    'notes'         => $validated['notes'] ?? null,
                    'created_by'    => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    if (empty($item['product_id'])) continue;
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $pr->id,
                        'product_id'          => $item['product_id'],
                        'quantity'            => $item['quantity'],
                        'estimated_unit_cost' => $item['estimated_unit_cost'] ?? 0,
                    ]);
                }
            });

            return redirect()->route('purchases.requests.index')
                ->with('success', 'Solicitud de compra creada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear solicitud de compra', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la solicitud.');
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorizeRecord($purchaseRequest);
        $purchaseRequest->load(['requestedBy', 'createdBy', 'items.product', 'quotations.supplier']);
        return view('purchases.requests.show', compact('purchaseRequest'));
    }

    public function updateStatus(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->authorizeRecord($purchaseRequest);
        $request->validate(['status' => 'required|in:' . implode(',', PurchaseRequest::STATUSES)]);

        $purchaseRequest->update(['status' => $request->status]);

        return back()->with('success', 'Estado actualizado a: ' . PurchaseRequest::STATUS_LABELS[$request->status]);
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $this->authorizeRecord($purchaseRequest);
        if (!in_array($purchaseRequest->status, ['borrador', 'rechazada', 'cancelada'])) {
            return back()->with('error', 'Solo se pueden eliminar solicitudes en borrador, rechazadas o canceladas.');
        }
        $purchaseRequest->delete();
        return redirect()->route('purchases.requests.index')->with('success', 'Solicitud eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(PurchaseRequest $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
