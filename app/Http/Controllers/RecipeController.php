<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Recetas de Producción
 *
 * Gestiona el CRUD completo de recetas. Cada receta define qué materias
 * primas (y en qué cantidades) se necesitan para producir un producto final.
 * Incluye un endpoint JSON para integrarse con el formulario de Producción.
 */
class RecipeController extends Controller
{
    /**
     * Lista las recetas de la empresa activa con filtro opcional por estado.
     * Paginado en 15 registros por página.
     */
    public function index(Request $request)
    {
        $companyId  = $this->getCompanyId();
        $activeStatus = $request->get('status', 'activa');

        $base = fn() => Recipe::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        $recipes = $base()
            ->with(['product', 'items', 'createdBy'])
            ->where('status', $activeStatus)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'borrador'  => $base()->where('status', 'borrador')->count(),
            'activa'    => $base()->where('status', 'activa')->count(),
            'inactiva'  => $base()->where('status', 'inactiva')->count(),
        ];

        return view('recipes.index', compact('recipes', 'activeStatus', 'counts'));
    }

    /**
     * Muestra el formulario para crear una nueva receta.
     * Precarga los productos finales y materias primas disponibles.
     */
    public function create()
    {
        $companyId = $this->getCompanyId();

        return view('recipes.create', [
            'recipe'        => null,
            'recipeNumber'  => Recipe::generateRecipeNumber($companyId),
            'finalProducts' => Product::where('company_id', $companyId)->where('active', true)->where('category', 'PRODUCTO FINAL')->orderBy('name')->get(),
            'rawMaterials'  => Product::with('measurementUnit')->where('company_id', $companyId)->where('active', true)->where('category', 'MATERIA PRIMA')->orderBy('name')->get(),
            'action'        => route('recipes.store'),
            'method'        => 'POST',
        ]);
    }

    /**
     * Almacena una nueva receta junto con sus ingredientes.
     * Usa una transacción para garantizar consistencia entre receta e ingredientes.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'             => 'required|string|max:255',
                'product_id'       => 'required|exists:products,id',
                'quantity_produced' => 'required|numeric|min:0.01',
                'status'           => 'required|in:borrador,activa,inactiva',
                'description'      => 'nullable|string',
                'items'            => 'nullable|array',
                'items.*.product_id'  => 'required_with:items|exists:products,id',
                'items.*.quantity'    => 'required_with:items|numeric|min:0.01',
                'items.*.unit_cost'   => 'required_with:items|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $recipe = Recipe::create([
                    'company_id'       => $companyId,
                    'product_id'       => $validated['product_id'],
                    'name'             => $validated['name'],
                    'recipe_number'    => Recipe::generateRecipeNumber($companyId),
                    'quantity_produced' => $validated['quantity_produced'],
                    'status'           => $validated['status'],
                    'description'      => $validated['description'] ?? null,
                    'created_by'       => auth()->id(),
                ]);

                foreach ($validated['items'] ?? [] as $item) {
                    if (empty($item['product_id'])) {
                        continue;
                    }
                    RecipeItem::create([
                        'recipe_id'  => $recipe->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_cost'  => $item['unit_cost'],
                        'total_cost' => $item['quantity'] * $item['unit_cost'],
                    ]);
                }
            });

            return redirect()->route('recipes.index')->with('success', 'Receta creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear receta', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la receta.');
        }
    }

    /**
     * Muestra el detalle completo de una receta: KPIs, ingredientes y descripción.
     */
    public function show(Recipe $recipe)
    {
        $this->authorizeRecord($recipe);
        $recipe->load(['product', 'items.product', 'createdBy', 'company']);

        return view('recipes.show', compact('recipe'));
    }

    /**
     * Muestra el formulario de edición con los ingredientes precargados.
     */
    public function edit(Recipe $recipe)
    {
        $this->authorizeRecord($recipe);
        $companyId = $this->getCompanyId();
        $recipe->load('items.product');

        return view('recipes.edit', [
            'recipe'        => $recipe,
            'recipeNumber'  => $recipe->recipe_number,
            'finalProducts' => Product::where('company_id', $companyId)->where('active', true)->where('category', 'PRODUCTO FINAL')->orderBy('name')->get(),
            'rawMaterials'  => Product::with('measurementUnit')->where('company_id', $companyId)->where('active', true)->where('category', 'MATERIA PRIMA')->orderBy('name')->get(),
            'action'        => route('recipes.update', $recipe),
            'method'        => 'PUT',
        ]);
    }

    /**
     * Actualiza la receta y sincroniza sus ingredientes:
     * elimina los anteriores y crea los nuevos en la misma transacción.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorizeRecord($recipe);

        try {
            $validated = $request->validate([
                'name'             => 'required|string|max:255',
                'product_id'       => 'required|exists:products,id',
                'quantity_produced' => 'required|numeric|min:0.01',
                'status'           => 'required|in:borrador,activa,inactiva',
                'description'      => 'nullable|string',
                'items'            => 'nullable|array',
                'items.*.product_id'  => 'required_with:items|exists:products,id',
                'items.*.quantity'    => 'required_with:items|numeric|min:0.01',
                'items.*.unit_cost'   => 'required_with:items|numeric|min:0',
            ]);

            DB::transaction(function () use ($validated, $recipe) {
                $recipe->update([
                    'product_id'       => $validated['product_id'],
                    'name'             => $validated['name'],
                    'quantity_produced' => $validated['quantity_produced'],
                    'status'           => $validated['status'],
                    'description'      => $validated['description'] ?? null,
                ]);

                // Sincronizar ingredientes: eliminar anteriores e insertar nuevos
                $recipe->items()->delete();

                foreach ($validated['items'] ?? [] as $item) {
                    if (empty($item['product_id'])) {
                        continue;
                    }
                    RecipeItem::create([
                        'recipe_id'  => $recipe->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_cost'  => $item['unit_cost'],
                        'total_cost' => $item['quantity'] * $item['unit_cost'],
                    ]);
                }
            });

            return redirect()->route('recipes.show', $recipe)->with('success', 'Receta actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar receta', ['recipe_id' => $recipe->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar la receta.');
        }
    }

    /**
     * Elimina (soft delete) una receta.
     * No se permite eliminar recetas con estado 'activa' para evitar
     * inconsistencias con producciones que la referencian.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorizeRecord($recipe);

        if ($recipe->status === 'activa') {
            return back()->with('error', 'No se puede eliminar una receta activa. Cambia su estado primero.');
        }

        try {
            $recipe->items()->delete();
            $recipe->delete();
            return redirect()->route('recipes.index')->with('success', 'Receta eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar receta', ['recipe_id' => $recipe->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la receta.');
        }
    }

    /**
     * Devuelve las recetas activas asociadas a un producto final en formato JSON.
     * Usado por el formulario de Producción para cargar recetas al elegir un producto.
     */
    public function byProduct(int $productId)
    {
        $companyId = $this->getCompanyId();

        $recipes = Recipe::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('status', 'activa')
            ->orderBy('name')
            ->get(['id', 'name', 'quantity_produced']);

        return response()->json($recipes);
    }

    /**
     * Devuelve los ingredientes de una receta en formato JSON.
     * Usado por el formulario de Producción para auto-rellenar materiales.
     */
    public function items(Recipe $recipe)
    {
        $this->authorizeRecord($recipe);
        $recipe->load('items.product');

        $data = $recipe->items->map(fn($item) => [
            'product_id'   => $item->product_id,
            'product_name' => $item->product->name,
            'quantity'     => (float) $item->quantity,
            'unit_cost'    => (float) $item->unit_cost,
            'total_cost'   => (float) $item->total_cost,
        ]);

        return response()->json([
            'product_id'       => $recipe->product_id,
            'quantity_produced' => (float) $recipe->quantity_produced,
            'items'            => $data,
        ]);
    }

    // ─── Métodos privados ─────────────────────────────────────────

    /**
     * Obtiene el ID de la empresa activa según el tipo de usuario.
     * El super administrador puede operar con cualquier empresa.
     */
    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    /**
     * Verifica que el registro pertenece a la empresa activa del usuario.
     * Lanza 403 si no coincide para evitar acceso entre empresas.
     */
    private function authorizeRecord(Recipe $recipe): void
    {
        if (!auth()->user()->is_super_admin && $recipe->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
