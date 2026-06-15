<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'supplier_number', 'type', 'name', 'commercial_name',
        'document_type', 'document_number', 'email', 'phone', 'mobile',
        'address', 'city', 'country', 'contact_name', 'contact_email',
        'contact_phone', 'payment_terms', 'credit_limit', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'deleted_at'   => 'datetime',
    ];

    const TYPES       = ['persona_natural', 'empresa'];
    const TYPE_LABELS = ['persona_natural' => 'Persona natural', 'empresa' => 'Empresa'];
    const TYPE_ICONS  = ['persona_natural' => 'bi-person', 'empresa' => 'bi-building'];

    const DOCUMENT_TYPES  = ['cedula', 'ruc', 'pasaporte', 'otro'];
    const DOCUMENT_LABELS = ['cedula' => 'Cédula', 'ruc' => 'RUC', 'pasaporte' => 'Pasaporte', 'otro' => 'Otro'];

    const STATUSES      = ['activo', 'inactivo'];
    const STATUS_LABELS = ['activo' => 'Activo', 'inactivo' => 'Inactivo'];
    const STATUS_COLORS = ['activo' => 'success', 'inactivo' => 'secondary'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseQuotations(): HasMany
    {
        return $this->hasMany(PurchaseQuotation::class);
    }

    public function accountsPayable(): HasMany
    {
        return $this->hasMany(AccountPayable::class);
    }

    public static function generateSupplierNumber(int $companyId): string
    {
        $prefix = 'PROV-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('supplier_number', 'like', $prefix . '%')
            ->orderByDesc('supplier_number')
            ->value('supplier_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getDisplayNameAttribute(): string
    {
        return ($this->type === 'empresa' && $this->commercial_name)
            ? $this->commercial_name
            : $this->name;
    }

    public function getPendingBalanceAttribute(): string
    {
        return $this->accountsPayable()
            ->whereIn('status', ['pendiente', 'pago_parcial', 'vencida'])
            ->sum('balance');
    }
}
