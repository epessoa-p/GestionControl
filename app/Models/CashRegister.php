<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'branch_id', 'name', 'code', 'active', 'created_by'];

    protected $casts = ['active' => 'boolean', 'deleted_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function sessions(): HasMany { return $this->hasMany(CashSession::class); }

    public function activeSession(): ?CashSession
    {
        return $this->sessions()->where('status', 'open')->latest('opened_at')->first();
    }
}
