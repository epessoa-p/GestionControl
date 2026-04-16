<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'petty_cash_id', 'type', 'amount', 'concept',
        'receipt_number', 'movement_date', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'movement_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    const TYPES = ['expense', 'replenishment'];
    const TYPE_LABELS = ['expense' => 'Gasto', 'replenishment' => 'Reposición'];
    const TYPE_COLORS = ['expense' => 'danger', 'replenishment' => 'success'];

    public function pettyCash(): BelongsTo { return $this->belongsTo(PettyCash::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
