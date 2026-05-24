<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MeasurementUnit;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $activeTab = request('tab', 'PRODUCTO FINAL');
        $companyId = $user->is_super_admin ? null : $user->getCurrentCompany()?->id;

        $base = fn() => Product::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        $products = $base()
            ->with(['company', 'measurementUnit'])
            ->where('category', $activeTab)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'PRODUCTO FINAL' => $base()->where('category', 'PRODUCTO FINAL')->count(),
            'MATERIA PRIMA'  => $base()->where('category', 'MATERIA PRIMA')->count(),
        ];

        return view('admin.products.index', compact('products', 'activeTab', 'counts'));
    }

    public function create()
    {
        $user = auth()->user();

        return view('admin.products.create', [
            'companies'       => $user->is_super_admin ? Company::orderBy('name')->get() : collect([$user->getCurrentCompany()])->filter(),
            'measurementUnits' => MeasurementUnit::query()->where('active', true)->orderBy('name')->get(),
            'defaultCategory' => request('category', ''),
        ]);
    }

    public function store()
    {
        try {
            $user = auth()->user();
            $companyId = $user->is_super_admin ? request('company_id') : $user->getCurrentCompany()?->id;

            $validated = request()->validate([
                'company_id' => ['nullable', 'exists:companies,id'],
                'name' => 'required|string|max:255',
                'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
                'description' => 'nullable|string',
                'measurement_unit_id' => ['required', 'exists:measurement_units,id'],
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'category' => ['nullable', 'string', 'max:50'],
                'current_stock' => ['nullable', 'numeric', 'min:0'],
                'min_stock' => ['nullable', 'numeric', 'min:0'],
                'active' => 'sometimes|boolean',
            ]);

            $measurementUnit = MeasurementUnit::findOrFail($validated['measurement_unit_id']);

            Product::create([
                ...$validated,
                'unit' => $measurementUnit->symbol ?: $measurementUnit->name,
                'company_id' => $companyId,
                'active' => request()->boolean('active', true),
            ]);

            return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
        } catch (\Throwable $exception) {
            Log::error('Error al crear producto', ['message' => $exception->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No fue posible crear el producto.']);
        }
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $user = auth()->user();

        return view('admin.products.edit', [
            'product' => $product,
            'companies' => $user->is_super_admin ? Company::orderBy('name')->get() : collect([$user->getCurrentCompany()])->filter(),
            'measurementUnits' => MeasurementUnit::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Product $product)
    {
        $this->authorizeProduct($product);

        try {
            $user = auth()->user();
            $companyId = $user->is_super_admin ? request('company_id', $product->company_id) : $product->company_id;

            $validated = request()->validate([
                'company_id' => ['nullable', 'exists:companies,id'],
                'name' => 'required|string|max:255',
                'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
                'description' => 'nullable|string',
                'measurement_unit_id' => ['required', 'exists:measurement_units,id'],
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'category' => ['nullable', 'string', 'max:50'],
                'current_stock' => ['nullable', 'numeric', 'min:0'],
                'min_stock' => ['nullable', 'numeric', 'min:0'],
                'active' => 'sometimes|boolean',
            ]);

            $measurementUnit = MeasurementUnit::findOrFail($validated['measurement_unit_id']);

            $product->update([
                ...$validated,
                'unit' => $measurementUnit->symbol ?: $measurementUnit->name,
                'company_id' => $companyId,
                'active' => request()->boolean('active', false),
            ]);

            return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
        } catch (\Throwable $exception) {
            Log::error('Error al actualizar producto', ['product_id' => $product->id, 'message' => $exception->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No fue posible actualizar el producto.']);
        }
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);

        try {
            $product->delete();
            return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Throwable $exception) {
            Log::error('Error al eliminar producto', ['product_id' => $product->id, 'message' => $exception->getMessage()]);
            return back()->withErrors(['error' => 'No fue posible eliminar el producto.']);
        }
    }

    protected function authorizeProduct(Product $product): void
    {
        if (!auth()->user()->is_super_admin && $product->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}