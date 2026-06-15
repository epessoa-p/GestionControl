<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPayablePayment extends Model
{
    protected $table = 'accounts_payable_payments';

    protected $fillable = [
        'accounts_payable_id', 'amount', 'payment_date', 'payment_method', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    const PAYMENT_METHODS = ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'otro'];
    const PAYMENT_METHOD_LABELS = [
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
        'cheque'        => 'Cheque',
        'tarjeta'       => 'Tarjeta',
        'otro'          => 'Otro',
    ];
    const PAYMENT_METHOD_ICONS = [
        'efectivo'      => 'bi-cash',
        'transferencia' => 'bi-arrow-left-right',
        'cheque'        => 'bi-file-earmark-text',
        'tarjeta'       => 'bi-credit-card',
        'otro'          => 'bi-three-dots',
    ];

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'accounts_payable_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
