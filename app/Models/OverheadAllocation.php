<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Asignación de Overhead a Producción
 *
 * Registra cuánto overhead se asignó a cada producción, con qué método
 * y desde qué período (o sin período si es tasa_fija/manual directo).
 */
class OverheadAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_id',
        'overhead_period_id',
        'amount',
        'method',
        'notes',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Producción a la que se asignó el overhead. */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    /** Período origen del overhead (puede ser null para tasa fija o manual directo). */
    public function period(): BelongsTo
    {
        return $this->belongsTo(OverheadPeriod::class, 'overhead_period_id');
    }
}
