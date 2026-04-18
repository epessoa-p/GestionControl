<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransferDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['warehouse_transfer_id', 'product_id', 'quantity', 'notes'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function transfer(): BelongsTo { return $this->belongsTo(WarehouseTransfer::class, 'warehouse_transfer_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
