<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_id', 'installment_number', 'due_date', 'amount', 'percentage',
        'paid_amount', 'status', 'paid_at', 'paid_by', 'payment_method', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['pending', 'partial', 'paid', 'overdue'];
    const STATUS_LABELS = [
        'pending' => 'Pendiente', 'partial' => 'Parcial',
        'paid' => 'Pagada', 'overdue' => 'Vencida',
    ];
    const STATUS_COLORS = [
        'pending' => 'warning', 'partial' => 'info',
        'paid' => 'success', 'overdue' => 'danger',
    ];

    const PAYMENT_METHODS = ['cash', 'card', 'transfer', 'other'];
    const PAYMENT_LABELS = [
        'cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'other' => 'Otro',
    ];

    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function paidBy(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }
}
