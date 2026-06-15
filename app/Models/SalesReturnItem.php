<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    protected $table = 'sales_return_items';

    protected $fillable = [
        'sales_return_id', 'product_id', 'quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function salesReturn(): BelongsTo { return $this->belongsTo(SalesReturn::class, 'sales_return_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
