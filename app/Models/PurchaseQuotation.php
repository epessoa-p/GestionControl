<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseQuotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'quotation_number', 'purchase_request_id', 'supplier_id',
        'quotation_date', 'valid_until', 'status', 'subtotal', 'tax', 'discount', 'total',
        'notes', 'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until'    => 'date',
        'subtotal'       => 'decimal:2',
        'tax'            => 'decimal:2',
        'discount'       => 'decimal:2',
        'total'          => 'decimal:2',
        'deleted_at'     => 'datetime',
    ];

    const STATUSES = ['borrador', 'enviada', 'recibida', 'aprobada', 'rechazada', 'cancelada'];
    const STATUS_LABELS = [
        'borrador'  => 'Borrador',
        'enviada'   => 'Enviada',
        'recibida'  => 'Recibida',
        'aprobada'  => 'Aprobada',
        'rechazada' => 'Rechazada',
        'cancelada' => 'Cancelada',
    ];
    const STATUS_COLORS = [
        'borrador'  => 'secondary',
        'enviada'   => 'info',
        'recibida'  => 'primary',
        'aprobada'  => 'success',
        'rechazada' => 'danger',
        'cancelada' => 'dark',
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

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseQuotationItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'purchase_quotation_id');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('quantity * unit_price - discount'));
        $tax      = round($subtotal * self::TAX_RATE, 2);
        $discount = $this->items()->sum('discount');
        $this->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'discount' => $discount,
            'total'    => $subtotal + $tax,
        ]);
    }

    public static function generateQuotationNumber(int $companyId): string
    {
        $prefix = 'COT-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('quotation_number', 'like', $prefix . '%')
            ->orderByDesc('quotation_number')
            ->value('quotation_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
