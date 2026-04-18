<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'transfer_number', 'from_warehouse_id', 'to_warehouse_id',
        'transfer_date', 'status', 'notes', 'total_items',
        'created_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'confirmed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['draft', 'in_transit', 'completed', 'cancelled'];
    const STATUS_LABELS = [
        'draft' => 'Borrador', 'in_transit' => 'En Tránsito',
        'completed' => 'Completado', 'cancelled' => 'Cancelado',
    ];
    const STATUS_COLORS = [
        'draft' => 'secondary', 'in_transit' => 'info',
        'completed' => 'success', 'cancelled' => 'danger',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function fromWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function confirmedBy(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function details(): HasMany { return $this->hasMany(WarehouseTransferDetail::class); }

    public static function generateNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->withTrashed()->max('transfer_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'TRA-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
