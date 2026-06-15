<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountPayable extends Model
{
    use SoftDeletes;

    protected $table = 'accounts_payable';

    protected $fillable = [
        'company_id', 'ap_number', 'supplier_id', 'purchase_order_id',
        'purchase_reception_id', 'invoice_number', 'invoice_date', 'due_date',
        'amount', 'amount_paid', 'balance', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'amount'       => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance'      => 'decimal:2',
        'deleted_at'   => 'datetime',
    ];

    const STATUSES = ['pendiente', 'pago_parcial', 'pagada', 'vencida', 'anulada'];
    const STATUS_LABELS = [
        'pendiente'    => 'Pendiente',
        'pago_parcial' => 'Pago parcial',
        'pagada'       => 'Pagada',
        'vencida'      => 'Vencida',
        'anulada'      => 'Anulada',
    ];
    const STATUS_COLORS = [
        'pendiente'    => 'warning',
        'pago_parcial' => 'info',
        'pagada'       => 'success',
        'vencida'      => 'danger',
        'anulada'      => 'secondary',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(PurchaseReception::class, 'purchase_reception_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountPayablePayment::class);
    }

    public static function generateApNumber(int $companyId): string
    {
        $prefix = 'CXP-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('ap_number', 'like', $prefix . '%')
            ->orderByDesc('ap_number')
            ->value('ap_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateBalance(): void
    {
        $paid    = $this->payments()->sum('amount');
        $balance = max(0, (float) $this->amount - $paid);

        $status = match (true) {
            $balance <= 0                                 => 'pagada',
            $paid > 0                                     => 'pago_parcial',
            $this->due_date < now()->toDateString()       => 'vencida',
            default                                       => 'pendiente',
        };

        $this->update([
            'amount_paid' => $paid,
            'balance'     => $balance,
            'status'      => $status,
        ]);
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['pagada', 'anulada'])
            && $this->due_date < now()->startOfDay();
    }
}
