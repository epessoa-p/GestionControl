<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPayablePayment extends Model
{
    protected $table = 'accounts_payable_payments';

    protected $fillable = [
        'accounts_payable_id', 'amount', 'payment_date', 'payment_method', 'reference', 'notes', 'created_by',
        'source', 'treasury_account_id', 'cash_session_id', 'cash_movement_id', 'treasury_movement_id',
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

    const SOURCE_LABELS = ['caja' => 'Caja abierta', 'tesoreria' => 'Tesorería'];

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'accounts_payable_id');
    }

    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class, 'treasury_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
