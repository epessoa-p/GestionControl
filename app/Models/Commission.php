<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'promoter_id', 'sale_id', 'amount', 'rate',
        'status', 'paid_at', 'paid_by', 'period_start', 'period_end',
        'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'paid_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['pending', 'paid'];
    const STATUS_LABELS = ['pending' => 'Pendiente', 'paid' => 'Pagada'];
    const STATUS_COLORS = ['pending' => 'warning', 'paid' => 'success'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function promoter(): BelongsTo { return $this->belongsTo(Promoter::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function paidBy(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
