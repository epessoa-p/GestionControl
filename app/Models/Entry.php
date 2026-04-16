<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'warehouse_id', 'entry_number', 'supplier',
        'entry_date', 'notes', 'status', 'total', 'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['draft', 'confirmed', 'cancelled'];
    const STATUS_LABELS = ['draft' => 'Borrador', 'confirmed' => 'Confirmada', 'cancelled' => 'Anulada'];
    const STATUS_COLORS = ['draft' => 'secondary', 'confirmed' => 'success', 'cancelled' => 'danger'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(EntryDetail::class); }

    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->details()->sum('total')]);
    }

    public static function generateNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->withTrashed()->max('entry_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'ENT-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
