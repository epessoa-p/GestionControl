<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'product_id', 'batch_number', 'quantity_produced',
        'production_date', 'status', 'total_cost', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'production_date' => 'date',
        'total_cost' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    const STATUSES = ['planned', 'in_progress', 'completed', 'cancelled'];
    const STATUS_LABELS = [
        'planned' => 'Planificada', 'in_progress' => 'En proceso',
        'completed' => 'Completada', 'cancelled' => 'Cancelada',
    ];
    const STATUS_COLORS = [
        'planned' => 'info', 'in_progress' => 'warning',
        'completed' => 'success', 'cancelled' => 'danger',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function costs(): HasMany { return $this->hasMany(ProductionCost::class); }
    public function materials(): HasMany { return $this->hasMany(ProductionMaterial::class); }

    public function recalculateTotalCost(): void
    {
        $costSum = $this->costs()->sum('amount');
        $materialSum = $this->materials()->sum('total_cost');
        $this->update(['total_cost' => $costSum + $materialSum]);
    }

    public static function generateBatchNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->withTrashed()->max('batch_number');
        $num = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;
        return 'PROD-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
