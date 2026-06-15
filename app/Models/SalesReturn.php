<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use SoftDeletes;

    protected $table = 'sales_returns';

    protected $fillable = [
        'company_id', 'return_number', 'sale_id', 'client_id', 'client_name',
        'warehouse_id', 'return_date', 'reason', 'status', 'total', 'notes', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total'       => 'decimal:2',
        'deleted_at'  => 'datetime',
    ];

    const STATUSES = ['borrador', 'confirmada', 'cancelada'];
    const STATUS_LABELS = ['borrador' => 'Borrador', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'];
    const STATUS_COLORS = ['borrador' => 'secondary', 'confirmada' => 'success', 'cancelada' => 'danger'];

    const REASONS = ['defectuoso', 'incorrecto', 'cliente', 'otro'];
    const REASON_LABELS = [
        'defectuoso' => 'Producto defectuoso',
        'incorrecto' => 'Producto incorrecto',
        'cliente'    => 'Decisión del cliente',
        'otro'       => 'Otro',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SalesReturnItem::class); }

    public function recalculateTotal(): void
    {
        $this->update(['total' => (float) $this->items()->sum('total')]);
    }

    public static function generateNumber(int $companyId): string
    {
        $prefix = 'DEVV-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
