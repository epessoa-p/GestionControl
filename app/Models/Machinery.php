<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Maquinaria / Activos Productivos
 *
 * Representa una máquina o equipo productivo con su costo y vida útil.
 * La depreciación mensual se calcula automáticamente: costo / vida_útil_meses.
 */
class Machinery extends Model
{
    use SoftDeletes;

    protected $table = 'machinery';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'cost',
        'useful_life_months',
        'purchase_date',
        'active',
        'created_by',
    ];

    protected $casts = [
        'cost'          => 'decimal:2',
        'purchase_date' => 'date',
        'active'        => 'boolean',
        'deleted_at'    => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Empresa propietaria de la maquinaria. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Usuario que registró la maquinaria. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Métodos de negocio ───────────────────────────────────────

    /**
     * Calcula la depreciación mensual por línea recta.
     * Retorna 0 si la vida útil es 0 para evitar división por cero.
     */
    public function monthlyDepreciation(): float
    {
        if ($this->useful_life_months <= 0) {
            return 0;
        }
        return (float) $this->cost / $this->useful_life_months;
    }

    /**
     * Calcula los meses restantes de vida útil desde la fecha de compra.
     * Retorna 0 si la maquinaria ya superó su vida útil.
     */
    public function remainingMonths(): int
    {
        $elapsed = (int) $this->purchase_date->diffInMonths(now());
        $remaining = $this->useful_life_months - $elapsed;
        return max(0, $remaining);
    }

    /**
     * Calcula la depreciación acumulada hasta la fecha.
     * Está acotada al costo total (no puede superar el valor original).
     */
    public function accumulatedDepreciation(): float
    {
        $elapsed = (int) $this->purchase_date->diffInMonths(now());
        $months  = min($elapsed, $this->useful_life_months);
        return $this->monthlyDepreciation() * $months;
    }
}
