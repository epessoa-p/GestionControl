<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_register_id', 'personal_id', 'opening_amount', 'closing_amount',
        'expected_amount', 'difference', 'status', 'opened_at', 'closed_at',
        'notes', 'opened_by', 'closed_by',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cashRegister(): BelongsTo { return $this->belongsTo(CashRegister::class); }
    public function personal(): BelongsTo { return $this->belongsTo(Personal::class); }
    public function openedBy(): BelongsTo { return $this->belongsTo(User::class, 'opened_by'); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function movements(): HasMany { return $this->hasMany(CashMovement::class); }

    public function calculateExpectedAmount(): float
    {
        $incomes = $this->movements()->where('type', 'income')->sum('amount');
        $expenses = $this->movements()->where('type', 'expense')->sum('amount');
        return (float) $this->opening_amount + $incomes - $expenses;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
