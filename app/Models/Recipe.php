<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Receta
 *
 * Representa una receta de producción que define qué materias primas
 * (y en qué cantidades) se necesitan para fabricar un producto final.
 */
class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_id',
        'name',
        'recipe_number',
        'quantity_produced',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'deleted_at'        => 'datetime',
    ];

    /** Estados posibles de una receta */
    const STATUSES = ['borrador', 'activa', 'inactiva'];

    /** Etiquetas en español para cada estado */
    const STATUS_LABELS = [
        'borrador'  => 'Borrador',
        'activa'    => 'Activa',
        'inactiva'  => 'Inactiva',
    ];

    /** Colores Bootstrap asociados a cada estado */
    const STATUS_COLORS = [
        'borrador'  => 'secondary',
        'activa'    => 'success',
        'inactiva'  => 'danger',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Empresa propietaria de la receta */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Producto final que genera esta receta */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Usuario que creó la receta */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Ingredientes (materias primas) que componen la receta */
    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    // ─── Métodos de dominio ───────────────────────────────────────

    /**
     * Recalcula el costo total de la receta sumando el total_cost
     * de todos sus ingredientes activos (sin soft-delete).
     */
    public function recalculateTotalCost(): void
    {
        // No hay columna total_cost en recipes; el costo se calcula
        // dinámicamente desde recipe_items cuando se necesita.
        // Este método existe para compatibilidad con el patrón del módulo.
    }

    /**
     * Genera un número único de receta con el formato REC-YYYYMM-XXXX.
     * Busca el último número registrado para la empresa (incluyendo eliminados)
     * y genera el siguiente correlativo.
     */
    public static function generateRecipeNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)
            ->withTrashed()
            ->max('recipe_number');

        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;

        return 'REC-' . date('Ym') . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
