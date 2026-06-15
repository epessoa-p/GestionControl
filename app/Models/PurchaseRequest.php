<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'request_number', 'requested_by', 'department',
        'priority', 'expected_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'deleted_at'    => 'datetime',
    ];

    const PRIORITIES      = ['baja', 'media', 'alta', 'urgente'];
    const PRIORITY_LABELS = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'];
    const PRIORITY_COLORS = ['baja' => 'secondary', 'media' => 'info', 'alta' => 'warning', 'urgente' => 'danger'];

    const STATUSES = ['borrador', 'pendiente', 'aprobada', 'rechazada', 'en_proceso', 'completada', 'cancelada'];
    const STATUS_LABELS = [
        'borrador'   => 'Borrador',
        'pendiente'  => 'Pendiente',
        'aprobada'   => 'Aprobada',
        'rechazada'  => 'Rechazada',
        'en_proceso' => 'En proceso',
        'completada' => 'Completada',
        'cancelada'  => 'Cancelada',
    ];
    const STATUS_COLORS = [
        'borrador'   => 'secondary',
        'pendiente'  => 'warning',
        'aprobada'   => 'success',
        'rechazada'  => 'danger',
        'en_proceso' => 'info',
        'completada' => 'primary',
        'cancelada'  => 'dark',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(PurchaseQuotation::class);
    }

    public static function generateRequestNumber(int $companyId): string
    {
        $prefix = 'SOL-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('request_number', 'like', $prefix . '%')
            ->orderByDesc('request_number')
            ->value('request_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getEstimatedTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->estimated_unit_cost);
    }
}
