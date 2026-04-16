<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promoter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'personal_id', 'name', 'phone',
        'email', 'commission_rate', 'active', 'created_by',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function personal(): BelongsTo { return $this->belongsTo(Personal::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
    public function commissions(): HasMany { return $this->hasMany(Commission::class); }
}
