<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Ítem de Gasto Indirecto
 *
 * Representa una línea de gasto dentro de un período de overhead.
 * Puede estar vinculado a una maquinaria (tipo depreciacion) o ser un gasto manual.
 */
class OverheadItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'overhead_period_id',
        'machinery_id',
        'concept',
        'category',
        'amount',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /** Categorías válidas de gasto indirecto. */
    const CATEGORIES = ['servicio', 'mano_de_obra', 'transporte', 'depreciacion', 'otro'];

    /** Etiquetas en español para cada categoría. */
    const CATEGORY_LABELS = [
        'servicio'      => 'Servicio',
        'mano_de_obra'  => 'Mano de obra',
        'transporte'    => 'Transporte',
        'depreciacion'  => 'Depreciación',
        'otro'          => 'Otro',
    ];

    /** Colores Bootstrap para cada categoría. */
    const CATEGORY_COLORS = [
        'servicio'      => 'primary',
        'mano_de_obra'  => 'success',
        'transporte'    => 'warning',
        'depreciacion'  => 'info',
        'otro'          => 'secondary',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Período al que pertenece este ítem. */
    public function period(): BelongsTo
    {
        return $this->belongsTo(OverheadPeriod::class, 'overhead_period_id');
    }

    /** Maquinaria asociada (solo cuando category = 'depreciacion'). */
    public function machinery(): BelongsTo
    {
        return $this->belongsTo(Machinery::class);
    }
}
