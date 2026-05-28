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
        'cash_session_id', 'type', 'category', 'amount', 'concept',
        'payment_method', 'reference', 'movement_date', 'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'movement_date' => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    const TYPES = ['income', 'expense'];
    const TYPE_LABELS = ['income' => 'Ingreso', 'expense' => 'Egreso'];
    const TYPE_COLORS = ['income' => 'success', 'expense' => 'danger'];
    const PAYMENT_METHODS = ['cash', 'card', 'transfer', 'other'];
    const PAYMENT_LABELS = ['cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'other' => 'Otro'];

    const CATEGORIES = [
        'sale'                 => ['label' => 'Venta',                 'type' => 'income'],
        'sale_return'          => ['label' => 'Devolución de venta',   'type' => 'expense'],
        'purchase_supplier'    => ['label' => 'Compra a proveedor',    'type' => 'expense'],
        'expense_operational'  => ['label' => 'Gasto operativo',       'type' => 'expense'],
        'expense_supplier'     => ['label' => 'Pago a proveedor',      'type' => 'expense'],
        'advance_customer'     => ['label' => 'Anticipo de cliente',   'type' => 'income'],
        'advance_return'       => ['label' => 'Devolución de anticipo','type' => 'expense'],
        'cash_adjustment_in'   => ['label' => 'Ajuste positivo',       'type' => 'income'],
        'cash_adjustment_out'  => ['label' => 'Ajuste negativo',       'type' => 'expense'],
        'other'                => ['label' => 'Otro',                  'type' => null],
    ];

    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
