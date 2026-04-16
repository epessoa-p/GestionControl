<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['production_id', 'product_id', 'quantity_used', 'unit_cost', 'total_cost'];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function production(): BelongsTo { return $this->belongsTo(Production::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
