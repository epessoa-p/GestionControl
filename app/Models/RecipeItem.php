<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo RecipeItem (Ingrediente de Receta)
 *
 * Representa un ingrediente dentro de una receta de producción,
 * indicando la materia prima requerida, la cantidad y el costo referencial.
 */
class RecipeItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Receta a la que pertenece este ingrediente */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** Materia prima (producto con categoría MATERIA PRIMA) */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
