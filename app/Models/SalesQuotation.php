<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuotation extends Model
{
    use SoftDeletes;

    protected $table = 'sales_quotations';

    protected $fillable = [
        'company_id', 'quotation_number', 'client_id', 'client_name', 'client_phone',
        'client_document', 'quotation_date', 'valid_until', 'status',
        'subtotal', 'tax', 'discount', 'total', 'sale_id', 'notes', 'created_by',
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

    const STATUSES = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'convertida'];
    const STATUS_LABELS = [
        'borrador'   => 'Borrador',
        'enviada'    => 'Enviada',
        'aprobada'   => 'Aprobada',
        'rechazada'  => 'Rechazada',
        'vencida'    => 'Vencida',
        'convertida' => 'Convertida',
    ];
    const STATUS_COLORS = [
        'borrador'   => 'secondary',
        'enviada'    => 'info',
        'aprobada'   => 'success',
        'rechazada'  => 'danger',
        'vencida'    => 'warning',
        'convertida' => 'primary',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SalesQuotationItem::class); }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $discount = (float) $this->items()->sum('discount');
        $this->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => $subtotal + (float) $this->tax,
        ]);
    }

    public static function generateNumber(int $companyId): string
    {
        $prefix = 'COTV-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('quotation_number', 'like', $prefix . '%')
            ->orderByDesc('quotation_number')
            ->value('quotation_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
