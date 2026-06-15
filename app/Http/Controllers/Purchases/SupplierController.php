<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');
        $search    = $request->get('q');

        $base = fn() => Supplier::where('company_id', $companyId);

        $suppliers = $base()
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('commercial_name', 'like', "%{$search}%")
                   ->orWhere('document_number', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhere('supplier_number', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'todos'    => $base()->count(),
            'activo'   => $base()->where('status', 'activo')->count(),
            'inactivo' => $base()->where('status', 'inactivo')->count(),
        ];

        return view('purchases.suppliers.index', compact('suppliers', 'status', 'counts', 'search'));
    }

    public function create()
    {
        $companyId      = $this->getCompanyId();
        $supplierNumber = Supplier::generateSupplierNumber($companyId);
        return view('purchases.suppliers.create', compact('supplierNumber'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);
        $companyId = $this->getCompanyId();

        try {
            Supplier::create(array_merge($validated, [
                'company_id'      => $companyId,
                'supplier_number' => Supplier::generateSupplierNumber($companyId),
                'created_by'      => auth()->id(),
            ]));
            return redirect()->route('purchases.suppliers.index')
                ->with('success', 'Proveedor registrado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear proveedor', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar el proveedor.');
        }
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeRecord($supplier);
        $supplier->load(['createdBy']);

        $orders   = $supplier->purchaseOrders()->with('createdBy')->latest()->take(10)->get();
        $payables = $supplier->accountsPayable()->with('payments')->latest()->take(10)->get();

        return view('purchases.suppliers.show', compact('supplier', 'orders', 'payables'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizeRecord($supplier);
        return view('purchases.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeRecord($supplier);
        $validated = $this->validateSupplier($request, $supplier->id);

        try {
            $supplier->update($validated);
            return redirect()->route('purchases.suppliers.show', $supplier)
                ->with('success', 'Proveedor actualizado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al actualizar proveedor', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar el proveedor.');
        }
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeRecord($supplier);
        try {
            $supplier->delete();
            return redirect()->route('purchases.suppliers.index')
                ->with('success', 'Proveedor eliminado.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No fue posible eliminar el proveedor.');
        }
    }

    private function validateSupplier(Request $request, ?int $excludeId = null): array
    {
        $companyId = $this->getCompanyId();
        return $request->validate([
            'type'           => 'required|in:' . implode(',', Supplier::TYPES),
            'name'           => 'required|string|max:255',
            'commercial_name'=> 'nullable|string|max:255',
            'document_type'  => 'nullable|in:' . implode(',', Supplier::DOCUMENT_TYPES),
            'document_number'=> [
                'nullable', 'string', 'max:50',
                Rule::unique('suppliers', 'document_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($excludeId),
            ],
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'mobile'         => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'contact_name'   => 'nullable|string|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:30',
            'payment_terms'  => 'nullable|integer|min:0|max:365',
            'credit_limit'   => 'nullable|numeric|min:0',
            'status'         => 'required|in:' . implode(',', Supplier::STATUSES),
            'notes'          => 'nullable|string',
        ]);
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(Supplier $supplier): void
    {
        if (!auth()->user()->is_super_admin && $supplier->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
