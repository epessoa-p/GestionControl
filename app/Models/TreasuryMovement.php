<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreasuryMovement extends Model
{
    use SoftDeletes;

    protected $table = 'treasury_movements';

    protected $fillable = [
        'treasury_account_id', 'company_id', 'type', 'category',
        'amount', 'description', 'reference', 'movement_date', 'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'movement_date' => 'date',
        'deleted_at'    => 'datetime',
    ];

    const CATEGORIES = [
        'aporte_capital'    => ['label' => 'Aporte de capital',       'type' => 'entrada'],
        'ingreso_venta'     => ['label' => 'Ingreso por venta',       'type' => 'entrada'],
        'deposito'          => ['label' => 'Depósito',                'type' => 'entrada'],
        'cobro_cuota'       => ['label' => 'Cobro de cuota',          'type' => 'entrada'],
        'transferencia_rec' => ['label' => 'Transferencia recibida',  'type' => 'entrada'],
        'otro_ingreso'      => ['label' => 'Otro ingreso',            'type' => 'entrada'],
        'pago_proveedor'    => ['label' => 'Pago a proveedor',        'type' => 'salida'],
        'pago_servicios'    => ['label' => 'Pago de servicios',       'type' => 'salida'],
        'gasto_operativo'   => ['label' => 'Gasto operativo',         'type' => 'salida'],
        'retiro'            => ['label' => 'Retiro',                  'type' => 'salida'],
        'transferencia_env' => ['label' => 'Transferencia enviada',   'type' => 'salida'],
        'otro_gasto'        => ['label' => 'Otro gasto',              'type' => 'salida'],
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class, 'treasury_account_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category]['label'] ?? $this->category;
    }
}
