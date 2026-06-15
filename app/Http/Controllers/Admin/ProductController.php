<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MeasurementUnit;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $activeTab   = request('tab', 'PRODUCTO FINAL');
        $warehouseId = request('warehouse_id');
        $companyId   = $user->is_super_admin ? null : $user->getCurrentCompany()?->id;

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

        $warehouses = Warehouse::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('active', true)->orderBy('name')->get();

        $warehouseStocks = collect();

        if ($warehouseId) {
            $productIds = $products->pluck('id');

            $rows = DB::table('warehouse_product_stocks')
                ->where('warehouse_id', $warehouseId)
                ->whereIn('product_id', $productIds)
                ->pluck('quantity', 'product_id');

            $warehouseStocks = $productIds->mapWithKeys(fn($id) => [
                $id => max(0, (float) ($rows[$id] ?? 0)),
            ]);
        }

        return view('admin.products.index', compact(
            'products', 'activeTab', 'counts',
            'warehouses', 'warehouseId', 'warehouseStocks'
        ));
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

    public function store(Request $request)
    {
        try {
            $user      = auth()->user();
            $companyId = $user->is_super_admin ? $request->input('company_id') : $user->getCurrentCompany()?->id;

            $validated = $request->validate([
                'company_id'          => ['nullable', 'exists:companies,id'],
                'name'                => 'required|string|max:255',
                'sku'                 => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
                'description'         => 'nullable|string',
                'measurement_unit_id' => ['required', 'exists:measurement_units,id'],
                'cost'                => 'required|numeric|min:0',
                'price'               => 'required|numeric|min:0',
                'category'            => ['nullable', 'string', 'max:50'],
                'current_stock'       => ['nullable', 'numeric', 'min:0'],
                'min_stock'           => ['nullable', 'numeric', 'min:0'],
                'active'              => 'sometimes|boolean',
                'images.*'            => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            ]);

            $measurementUnit = MeasurementUnit::findOrFail($validated['measurement_unit_id']);

            $product = Product::create([
                ...$validated,
                'unit'       => $measurementUnit->symbol ?: $measurementUnit->name,
                'company_id' => $companyId,
                'active'     => $request->boolean('active', true),
            ]);

            $this->handleImageUploads($request, $product, (int) $request->input('primary_image_index', 0));

            return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear producto', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No fue posible crear el producto.']);
        }
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $user = auth()->user();
        $product->load('images');

        return view('admin.products.edit', [
            'product'          => $product,
            'companies'        => $user->is_super_admin ? Company::orderBy('name')->get() : collect([$user->getCurrentCompany()])->filter(),
            'measurementUnits' => MeasurementUnit::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        try {
            $user      = auth()->user();
            $companyId = $user->is_super_admin ? $request->input('company_id', $product->company_id) : $product->company_id;

            $validated = $request->validate([
                'company_id'          => ['nullable', 'exists:companies,id'],
                'name'                => 'required|string|max:255',
                'sku'                 => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
                'description'         => 'nullable|string',
                'measurement_unit_id' => ['required', 'exists:measurement_units,id'],
                'cost'                => 'required|numeric|min:0',
                'price'               => 'required|numeric|min:0',
                'category'            => ['nullable', 'string', 'max:50'],
                'current_stock'       => ['nullable', 'numeric', 'min:0'],
                'min_stock'           => ['nullable', 'numeric', 'min:0'],
                'active'              => 'sometimes|boolean',
                'images.*'            => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            ]);

            $measurementUnit = MeasurementUnit::findOrFail($validated['measurement_unit_id']);

            $product->update([
                ...$validated,
                'unit'       => $measurementUnit->symbol ?: $measurementUnit->name,
                'company_id' => $companyId,
                'active'     => $request->boolean('active', false),
            ]);

            $primaryIdx = (int) $request->input('primary_image_index', 0);
            $hasExisting = $product->images()->exists();
            $this->handleImageUploads($request, $product, $primaryIdx, !$hasExisting);

            return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al actualizar producto', ['product_id' => $product->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No fue posible actualizar el producto.']);
        }
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        $this->authorizeProduct($product);

        if ($image->product_id !== $product->id) {
            abort(404);
        }

        try {
            Storage::disk('public')->delete($image->filename);
            $image->delete();

            // Si era la principal, promover la siguiente
            if ($image->is_primary) {
                $next = $product->images()->first();
                $next?->update(['is_primary' => true]);
            }

            return back()->with('success', 'Imagen eliminada.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar imagen de producto', ['image_id' => $image->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la imagen.');
        }
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        $this->authorizeProduct($product);

        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Imagen principal actualizada.');
    }

    private function handleImageUploads(Request $request, Product $product, int $primaryIndex = 0, bool $allowSetPrimary = true): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $files      = $request->file('images');
        $hasPrimary = !$allowSetPrimary || $product->images()->where('is_primary', true)->exists();

        foreach ($files as $idx => $file) {
            $path = $file->store('products', 'public');
            $isPrimary = !$hasPrimary && ($idx === $primaryIndex);

            ProductImage::create([
                'product_id'    => $product->id,
                'filename'      => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'is_primary'    => $isPrimary,
                'sort_order'    => $product->images()->max('sort_order') + $idx + 1,
            ]);

            if ($isPrimary) {
                $hasPrimary = true;
            }
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