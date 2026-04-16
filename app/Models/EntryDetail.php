<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntryDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['entry_id', 'product_id', 'quantity', 'unit_cost', 'total'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function entry(): BelongsTo { return $this->belongsTo(Entry::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
