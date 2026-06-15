<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'return_number', 'purchase_reception_id', 'supplier_id',
        'return_date', 'reason', 'status', 'total', 'notes', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total'       => 'decimal:2',
        'deleted_at'  => 'datetime',
    ];

    const REASONS       = ['defectuoso', 'incorrecto', 'exceso', 'otro'];
    const REASON_LABELS = [
        'defectuoso' => 'Defectuoso',
        'incorrecto' => 'Incorrecto',
        'exceso'     => 'Exceso de stock',
        'otro'       => 'Otro',
    ];

    const STATUSES      = ['borrador', 'confirmada', 'cancelada'];
    const STATUS_LABELS = ['borrador' => 'Borrador', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'];
    const STATUS_COLORS = ['borrador' => 'secondary', 'confirmada' => 'warning', 'cancelada' => 'danger'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(PurchaseReception::class, 'purchase_reception_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public static function generateReturnNumber(int $companyId): string
    {
        $prefix = 'DEV-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function confirm(): void
    {
        DB::transaction(function () {
            foreach ($this->items as $item) {
                $item->product->decrement('current_stock', (float) $item->quantity);
            }
            $total = $this->items->sum(fn($i) => (float) $i->quantity * (float) $i->unit_price);
            $this->update(['status' => 'confirmada', 'total' => $total]);
        });
    }
}
