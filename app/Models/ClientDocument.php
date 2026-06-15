<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientDocument extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'size',
    ];

    const TYPES = ['ci_anverso', 'ci_reverso', 'factura', 'otro'];

    const TYPE_LABELS = [
        'ci_anverso' => 'CI Anverso',
        'ci_reverso' => 'CI Reverso',
        'factura'    => 'Factura',
        'otro'       => 'Otro',
    ];

    const TYPE_ICONS = [
        'ci_anverso' => 'bi-credit-card',
        'ci_reverso' => 'bi-credit-card-2-back',
        'factura'    => 'bi-receipt',
        'otro'       => 'bi-file-earmark-image',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->filename);
    }
}
