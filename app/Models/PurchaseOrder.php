<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'order_number', 'supplier_id', 'purchase_quotation_id',
        'warehouse_id', 'order_date', 'expected_date', 'status',
        'subtotal', 'tax', 'discount', 'total', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'subtotal'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'discount'      => 'decimal:2',
        'total'         => 'decimal:2',
        'deleted_at'    => 'datetime',
    ];

    const STATUSES = ['borrador', 'aprobada', 'recibida_parcial', 'recibida', 'cancelada'];
    const STATUS_LABELS = [
        'borrador'          => 'Borrador',
        'aprobada'          => 'Aprobada',
        'recibida_parcial'  => 'Recibida parcial',
        'recibida'          => 'Recibida',
        'cancelada'         => 'Cancelada',
    ];
    const STATUS_COLORS = [
        'borrador'          => 'secondary',
        'aprobada'          => 'success',
        'recibida_parcial'  => 'warning',
        'recibida'          => 'primary',
        'cancelada'         => 'danger',
    ];

    const TAX_RATE = 0.12;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(PurchaseQuotation::class, 'purchase_quotation_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(PurchaseReception::class);
    }

    public function accountPayable(): HasOne
    {
        return $this->hasOne(AccountPayable::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('(unit_price * quantity) - discount'));
        $tax      = round($subtotal * self::TAX_RATE, 2);
        $discount = $this->items()->sum('discount');
        $this->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'discount' => $discount,
            'total'    => $subtotal + $tax,
        ]);
    }

    public function updateReceptionProgress(): void
    {
        $totalQty    = $this->items()->sum('quantity');
        $receivedQty = $this->items()->sum('quantity_received');

        if ($receivedQty <= 0) {
            return;
        }

        $newStatus = $receivedQty >= $totalQty ? 'recibida' : 'recibida_parcial';

        if (!in_array($this->status, ['recibida', 'cancelada'])) {
            $this->update(['status' => $newStatus]);
        }
    }

    public static function generateOrderNumber(int $companyId): string
    {
        $prefix = 'OC-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
