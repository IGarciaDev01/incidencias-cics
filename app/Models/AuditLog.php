<?php

namespace App\Models;

use App\Enums\AuditAction;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'actor_user_id',
        'actor_type',
        'actor_identifier',
        'action',
        'subject_type',
        'subject_id',
        'incidencia_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'route_name',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }
}
