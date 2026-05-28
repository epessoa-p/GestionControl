<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Período de Gastos Indirectos (Overhead)
 *
 * Agrupa todos los costos indirectos de un período (mes, semana, etc.):
 * servicios, mano de obra, transporte, depreciación de maquinaria.
 * Soporta 4 métodos de distribución hacia las producciones del período.
 */
class OverheadPeriod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'period_start',
        'period_end',
        'status',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'total_amount' => 'decimal:2',
        'deleted_at'   => 'datetime',
    ];

    /** Estados del período. */
    const STATUSES = ['abierto', 'cerrado'];

    /** Etiquetas en español para los estados. */
    const STATUS_LABELS = [
        'abierto'  => 'Abierto',
        'cerrado'  => 'Cerrado',
    ];

    /** Colores Bootstrap para los estados. */
    const STATUS_COLORS = [
        'abierto'  => 'success',
        'cerrado'  => 'secondary',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    /** Empresa propietaria del período. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Usuario que creó el período. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Ítems de gasto registrados en el período. */
    public function items(): HasMany
    {
        return $this->hasMany(OverheadItem::class, 'overhead_period_id');
    }

    /** Asignaciones realizadas a producciones desde este período. */
    public function allocations(): HasMany
    {
        return $this->hasMany(OverheadAllocation::class, 'overhead_period_id');
    }

    // ─── Métodos de negocio ───────────────────────────────────────

    /**
     * Recalcula el total sumando todos los ítems activos y actualiza el registro.
     */
    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('amount')]);
    }

    /**
     * Devuelve el monto total ya asignado a producciones desde este período.
     * Usa la relación cargada en memoria si existe, evitando consultas N+1.
     */
    public function allocatedAmount(): float
    {
        if ($this->relationLoaded('allocations')) {
            return (float) $this->allocations->sum('amount');
        }
        return (float) $this->allocations()->sum('amount');
    }

    /**
     * Devuelve el monto pendiente de asignar (total - asignado).
     */
    public function pendingAmount(): float
    {
        return max(0, (float) $this->total_amount - $this->allocatedAmount());
    }

    /**
     * Calcula el overhead sugerido para una producción según el método configurado.
     *
     * Métodos:
     *  - por_unidades: distribución proporcional a las unidades producidas en el período.
     *  - por_orden:    distribución uniforme entre todas las órdenes del período.
     *  - tasa_fija:    tasa configurada en la empresa × unidades de la producción.
     *  - manual:       devuelve el monto pendiente como sugerencia editable.
     */
    public function suggestedAllocation(Production $production, string $method): float
    {
        switch ($method) {
            case 'por_unidades':
                $totalUnits = Production::where('company_id', $this->company_id)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->whereBetween('production_date', [$this->period_start, $this->period_end])
                    ->sum('quantity_produced');
                if ($totalUnits <= 0) {
                    return 0;
                }
                return round((float) $this->total_amount / $totalUnits * $production->quantity_produced, 2);

            case 'por_orden':
                $totalOrders = Production::where('company_id', $this->company_id)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->whereBetween('production_date', [$this->period_start, $this->period_end])
                    ->count();
                if ($totalOrders <= 0) {
                    return 0;
                }
                return round((float) $this->total_amount / $totalOrders, 2);

            case 'tasa_fija':
                $rate = (float) ($this->company->overhead_fixed_rate ?? 0);
                return round($rate * $production->quantity_produced, 2);

            case 'manual':
            default:
                return $this->pendingAmount();
        }
    }
}
