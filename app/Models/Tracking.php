<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tracking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'title', 'description', 'type', 'status',
        'priority', 'due_date', 'completed_at', 'assigned_to', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const TYPES = ['operation', 'client', 'sale', 'internal'];
    const STATUSES = ['pending', 'in_progress', 'completed'];
    const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    const STATUS_LABELS = [
        'pending' => 'Pendiente', 'in_progress' => 'En proceso', 'completed' => 'Finalizado',
    ];
    const STATUS_COLORS = [
        'pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success',
    ];
    const PRIORITY_LABELS = [
        'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente',
    ];
    const PRIORITY_COLORS = [
        'low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger',
    ];
    const TYPE_LABELS = [
        'operation' => 'Operación', 'client' => 'Cliente', 'sale' => 'Venta', 'internal' => 'Interno',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
