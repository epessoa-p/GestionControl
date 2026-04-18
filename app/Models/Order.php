<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'order_number', 'order_type',
        'client_name', 'client_phone', 'client_document', 'client_email', 'client_address',
        'order_date', 'expected_date', 'delivered_date',
        'subtotal', 'tax', 'discount', 'total',
        'status', 'priority', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'delivered_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const ORDER_TYPES = ['purchase', 'customer'];
    const ORDER_TYPE_LABELS = ['purchase' => 'Compra', 'customer' => 'Cliente'];

    const STATUSES = ['draft', 'confirmed', 'in_process', 'shipped', 'delivered', 'cancelled'];
    const STATUS_LABELS = [
        'draft' => 'Borrador', 'confirmed' => 'Confirmado', 'in_process' => 'En Proceso',
        'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado',
    ];
    const STATUS_COLORS = [
        'draft' => 'secondary', 'confirmed' => 'primary', 'in_process' => 'info',
        'shipped' => 'warning', 'delivered' => 'success', 'cancelled' => 'danger',
    ];

    const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    const PRIORITY_LABELS = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
    const PRIORITY_COLORS = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(OrderDetail::class); }

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
        $last = static::where('company_id', $companyId)->withTrashed()->max('order_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'ORD-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
