<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesQuotationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = SalesQuotation::with(['client', 'createdBy'])
            ->where('company_id', $companyId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('quotation_number', 'like', '%' . $request->q . '%');
        }

        return view('sales.quotations.index', [
            'quotations' => $query->paginate(15)->withQueryString(),
            'filters'    => $request->only(['status', 'q']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('sales.quotations.form', [
            'quotation' => null,
            'clients'   => Client::where('company_id', $companyId)->orderBy('name')->get(),
            'products'  => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber'=> SalesQuotation::generateNumber($companyId),
            'action'    => route('sales-quotations.store'),
            'method'    => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $companyId = $this->getCompanyId();

        try {
            $quotation = DB::transaction(function () use ($validated, $companyId) {
                $quotation = SalesQuotation::create([
                    'company_id'       => $companyId,
                    'quotation_number' => SalesQuotation::generateNumber($companyId),
                    'client_id'        => $validated['client_id'] ?? null,
                    'client_name'      => $validated['client_name'] ?? null,
                    'client_phone'     => $validated['client_phone'] ?? null,
                    'client_document'  => $validated['client_document'] ?? null,
                    'quotation_date'   => $validated['quotation_date'],
                    'valid_until'      => $validated['valid_until'] ?? null,
                    'status'           => $validated['status'] ?? 'borrador',
                    'tax'              => $validated['tax'] ?? 0,
                    'notes'            => $validated['notes'] ?? null,
                    'created_by'       => auth()->id(),
                ]);

                $this->syncItems($quotation, $validated['items']);
                $quotation->recalculateTotals();
                return $quotation;
            });

            return redirect()->route('sales-quotations.show', $quotation)->with('success', 'Cotización creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear cotización de venta', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la cotización.');
        }
    }

    public function show(SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        $salesQuotation->load(['items.product', 'client', 'createdBy', 'sale']);
        return view('sales.quotations.show', ['quotation' => $salesQuotation]);
    }

    public function edit(SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        if (in_array($salesQuotation->status, ['convertida'])) {
            return back()->with('error', 'No se puede editar una cotización convertida.');
        }
        $companyId = $this->getCompanyId();
        $salesQuotation->load('items');
        return view('sales.quotations.form', [
            'quotation' => $salesQuotation,
            'clients'   => Client::where('company_id', $companyId)->orderBy('name')->get(),
            'products'  => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber'=> $salesQuotation->quotation_number,
            'action'    => route('sales-quotations.update', $salesQuotation),
            'method'    => 'PUT',
        ]);
    }

    public function update(Request $request, SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        $validated = $this->validateData($request);

        try {
            DB::transaction(function () use ($validated, $salesQuotation) {
                $salesQuotation->update([
                    'client_id'       => $validated['client_id'] ?? null,
                    'client_name'     => $validated['client_name'] ?? null,
                    'client_phone'    => $validated['client_phone'] ?? null,
                    'client_document' => $validated['client_document'] ?? null,
                    'quotation_date'  => $validated['quotation_date'],
                    'valid_until'     => $validated['valid_until'] ?? null,
                    'status'          => $validated['status'] ?? $salesQuotation->status,
                    'tax'             => $validated['tax'] ?? 0,
                    'notes'           => $validated['notes'] ?? null,
                ]);

                $salesQuotation->items()->delete();
                $this->syncItems($salesQuotation, $validated['items']);
                $salesQuotation->recalculateTotals();
            });

            return redirect()->route('sales-quotations.show', $salesQuotation)->with('success', 'Cotización actualizada.');
        } catch (\Throwable $e) {
            Log::error('Error al actualizar cotización de venta', ['id' => $salesQuotation->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar la cotización.');
        }
    }

    public function updateStatus(Request $request, SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        $validated = $request->validate(['status' => 'required|in:borrador,enviada,aprobada,rechazada,vencida']);
        $salesQuotation->update(['status' => $validated['status']]);
        return back()->with('success', 'Estado de la cotización actualizado.');
    }

    public function convertToSale(SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        if ($salesQuotation->status === 'convertida' || $salesQuotation->sale_id) {
            return back()->with('error', 'Esta cotización ya fue convertida en venta.');
        }

        try {
            $sale = DB::transaction(function () use ($salesQuotation) {
                $sale = Sale::create([
                    'company_id'      => $salesQuotation->company_id,
                    'client_id'       => $salesQuotation->client_id,
                    'sale_number'     => Sale::generateNumber($salesQuotation->company_id),
                    'sale_date'       => now()->toDateString(),
                    'client_name'     => $salesQuotation->client_name,
                    'client_phone'    => $salesQuotation->client_phone,
                    'client_document' => $salesQuotation->client_document,
                    'payment_method'  => 'cash',
                    'sale_type'       => 'cash',
                    'subtotal'        => $salesQuotation->subtotal,
                    'tax'             => $salesQuotation->tax,
                    'discount'        => $salesQuotation->discount,
                    'total'           => $salesQuotation->total,
                    'status'          => 'pending',
                    'notes'           => 'Generada desde cotización ' . $salesQuotation->quotation_number,
                    'created_by'      => auth()->id(),
                ]);

                foreach ($salesQuotation->items as $item) {
                    SaleDetail::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount'   => $item->discount,
                        'total'      => $item->total,
                    ]);
                }

                $salesQuotation->update(['status' => 'convertida', 'sale_id' => $sale->id]);
                return $sale;
            });

            return redirect()->route('sales.show', $sale)->with('success', 'Cotización convertida en venta. Complétala para descontar inventario.');
        } catch (\Throwable $e) {
            Log::error('Error al convertir cotización en venta', ['id' => $salesQuotation->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible convertir la cotización.');
        }
    }

    public function destroy(SalesQuotation $salesQuotation)
    {
        $this->authorizeRecord($salesQuotation);
        $salesQuotation->items()->delete();
        $salesQuotation->delete();
        return redirect()->route('sales-quotations.index')->with('success', 'Cotización eliminada.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id'       => 'nullable|exists:clients,id',
            'client_name'     => 'nullable|string|max:255',
            'client_phone'    => 'nullable|string|max:50',
            'client_document' => 'nullable|string|max:50',
            'quotation_date'  => 'required|date',
            'valid_until'     => 'nullable|date',
            'status'          => 'nullable|in:borrador,enviada,aprobada,rechazada,vencida',
            'tax'             => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.discount'     => 'nullable|numeric|min:0',
        ]);
    }

    private function syncItems(SalesQuotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $discount = $item['discount'] ?? 0;
            SalesQuotationItem::create([
                'sales_quotation_id' => $quotation->id,
                'product_id'         => $item['product_id'],
                'quantity'           => $item['quantity'],
                'unit_price'         => $item['unit_price'],
                'discount'           => $discount,
                'total'              => ($item['quantity'] * $item['unit_price']) - $discount,
            ]);
        }
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
