<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo de Cliente (CRM)
 *
 * Representa un cliente de la empresa: persona natural o empresa.
 * Incluye datos de contacto, ubicación, estado CRM y origen de captación.
 * Soporta múltiples contactos adicionales para clientes tipo empresa.
 */
class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_number',
        'type',
        'name',
        'commercial_name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'country',
        'status',
        'source',
        'assigned_to',
        'notes',
        'created_by',
        'photo',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'latitude'   => 'decimal:8',
        'longitude'  => 'decimal:8',
    ];

    // ─── Constantes de negocio ────────────────────────────────

    const TYPES = ['persona_natural', 'empresa'];
    const TYPE_LABELS = [
        'persona_natural' => 'Persona natural',
        'empresa'         => 'Empresa',
    ];
    const TYPE_ICONS = [
        'persona_natural' => 'bi-person',
        'empresa'         => 'bi-building',
    ];

    const DOCUMENT_TYPES = ['cedula', 'ruc', 'pasaporte', 'otro'];
    const DOCUMENT_LABELS = [
        'cedula'   => 'Cédula',
        'ruc'      => 'RUC',
        'pasaporte'=> 'Pasaporte',
        'otro'     => 'Otro',
    ];

    const STATUSES = ['activo', 'inactivo', 'prospecto', 'bloqueado'];
    const STATUS_LABELS = [
        'activo'    => 'Activo',
        'inactivo'  => 'Inactivo',
        'prospecto' => 'Prospecto',
        'bloqueado' => 'Bloqueado',
    ];
    const STATUS_COLORS = [
        'activo'    => 'success',
        'inactivo'  => 'secondary',
        'prospecto' => 'info',
        'bloqueado' => 'danger',
    ];

    const SOURCES = ['directo', 'referido', 'web', 'redes_sociales', 'feria', 'otro'];
    const SOURCE_LABELS = [
        'directo'       => 'Directo',
        'referido'      => 'Referido',
        'web'           => 'Web / Online',
        'redes_sociales'=> 'Redes sociales',
        'feria'         => 'Feria / Evento',
        'otro'          => 'Otro',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    /** Empresa propietaria del registro. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Usuario que creó el cliente. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Asesor / vendedor asignado. */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Contactos adicionales (para clientes tipo empresa). */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /** Contacto principal (si existe). */
    public function primaryContact(): ?ClientContact
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /** Documentos adjuntos (CI, factura, etc.). */
    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class)->orderBy('type');
    }

    /** Documento de un tipo específico (uno por tipo). */
    public function documentByType(string $type): ?ClientDocument
    {
        return $this->documents->firstWhere('type', $type);
    }

    /** URL pública de la foto del cliente. */
    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    // ─── Métodos de negocio ───────────────────────────────────

    /**
     * Genera un número de cliente único con formato CLI-YYYYMM-XXXX.
     */
    public static function generateClientNumber(int $companyId): string
    {
        $prefix = 'CLI-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('client_number', 'like', $prefix . '%')
            ->orderByDesc('client_number')
            ->value('client_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Nombre para mostrar: usa commercial_name si es empresa y está disponible.
     */
    public function getDisplayNameAttribute(): string
    {
        return ($this->type === 'empresa' && $this->commercial_name)
            ? $this->commercial_name
            : $this->name;
    }
}
