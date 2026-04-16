<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_session_id', 'type', 'amount', 'concept',
        'payment_method', 'reference', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const TYPES = ['income', 'expense'];
    const TYPE_LABELS = ['income' => 'Ingreso', 'expense' => 'Egreso'];
    const TYPE_COLORS = ['income' => 'success', 'expense' => 'danger'];
    const PAYMENT_METHODS = ['cash', 'card', 'transfer', 'other'];
    const PAYMENT_LABELS = ['cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'other' => 'Otro'];

    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
