<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreasuryAccount extends Model
{
    use SoftDeletes;

    protected $table = 'treasury_accounts';

    protected $fillable = [
        'company_id', 'name', 'type', 'bank_name', 'account_number',
        'initial_balance', 'current_balance', 'color', 'active', 'notes', 'created_by',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'active'          => 'boolean',
        'deleted_at'      => 'datetime',
    ];

    const TYPES = ['banco', 'efectivo', 'otro'];

    const TYPE_LABELS = [
        'banco'    => 'Banco',
        'efectivo' => 'Efectivo',
        'otro'     => 'Otro',
    ];

    const TYPE_ICONS = [
        'banco'    => 'bank',
        'efectivo' => 'cash-coin',
        'otro'     => 'wallet',
    ];

    const TYPE_COLORS = [
        'banco'    => '#3b82f6',
        'efectivo' => '#16a34a',
        'otro'     => '#6b7280',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(TreasuryMovement::class);
    }

    public function recalculateBalance(): void
    {
        $entradas = $this->movements()->where('type', 'entrada')->sum('amount');
        $salidas  = $this->movements()->where('type', 'salida')->sum('amount');

        $this->update([
            'current_balance' => (float) $this->initial_balance + $entradas - $salidas,
        ]);
    }
}
