<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCash extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'initial_amount',
        'current_balance', 'active', 'created_by',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function movements(): HasMany { return $this->hasMany(PettyCashMovement::class); }

    public function recalculateBalance(): void
    {
        $replenishments = $this->movements()->where('type', 'replenishment')->sum('amount');
        $expenses = $this->movements()->where('type', 'expense')->sum('amount');
        $this->update(['current_balance' => $this->initial_amount + $replenishments - $expenses]);
    }
}
