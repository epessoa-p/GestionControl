<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'warehouse_id', 'departure_number', 'reason',
        'departure_date', 'notes', 'status', 'total',
        'reference_type', 'reference_id', 'created_by',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const REASONS = ['sale', 'production', 'transfer', 'damage', 'other'];
    const REASON_LABELS = [
        'sale' => 'Venta', 'production' => 'Producción', 'transfer' => 'Transferencia',
        'damage' => 'Daño/Merma', 'other' => 'Otro',
    ];
    const STATUSES = ['draft', 'confirmed', 'cancelled'];
    const STATUS_LABELS = ['draft' => 'Borrador', 'confirmed' => 'Confirmada', 'cancelled' => 'Anulada'];
    const STATUS_COLORS = ['draft' => 'secondary', 'confirmed' => 'success', 'cancelled' => 'danger'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(DepartureDetail::class); }

    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->details()->sum('total')]);
    }

    public static function generateNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->withTrashed()->max('departure_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'SAL-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
