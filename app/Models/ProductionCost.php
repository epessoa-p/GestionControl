<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionCost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['production_id', 'concept', 'type', 'amount'];

    protected $casts = ['amount' => 'decimal:2', 'deleted_at' => 'datetime'];

    const TYPES = ['direct', 'indirect'];
    const TYPE_LABELS = ['direct' => 'Directo', 'indirect' => 'Indirecto'];

    public function production(): BelongsTo { return $this->belongsTo(Production::class); }
}
