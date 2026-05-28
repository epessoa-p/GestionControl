<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Contacto de Cliente
 *
 * Persona de contacto adicional dentro de un cliente tipo empresa.
 * Un cliente puede tener múltiples contactos; uno puede marcarse como principal.
 */
class ClientContact extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    /** Cliente al que pertenece este contacto. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
