<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'promoter_id',
        'cash_session_id', 'sale_number', 'client_name', 'client_phone',
        'client_document', 'sale_date', 'subtotal', 'tax', 'discount',
        'total', 'payment_method', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['pending', 'completed', 'cancelled'];
    const STATUS_LABELS = ['pending' => 'Pendiente', 'completed' => 'Completada', 'cancelled' => 'Cancelada'];
    const STATUS_COLORS = ['pending' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
    const PAYMENT_METHODS = ['cash', 'card', 'transfer', 'credit', 'other'];
    const PAYMENT_LABELS = [
        'cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia',
        'credit' => 'Crédito', 'other' => 'Otro',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function promoter(): BelongsTo { return $this->belongsTo(Promoter::class); }
    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(SaleDetail::class); }
    public function commissions(): HasMany { return $this->hasMany(Commission::class); }

    public function recalculateTotals(): void
    {
        $subtotal = $this->details()->sum('total');
        $this->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $this->tax - $this->discount,
        ]);
    }

    public static function generateNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->withTrashed()->max('sale_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'VTA-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
