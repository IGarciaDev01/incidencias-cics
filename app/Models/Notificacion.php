<?php

namespace App\Models;

use App\Enums\TipoNotificacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    const UPDATED_AT = null;

    protected $fillable = [
        'incidencia_id',
        'user_id',
        'destinatario_email',
        'tipo',
        'asunto',
        'enviada_at',
        'leida_at',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoNotificacion::class,
            'enviada_at' => 'datetime',
            'leida_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fueEnviada(): bool
    {
        return $this->enviada_at !== null;
    }

    public function fueLeida(): bool
    {
        return $this->leida_at !== null;
    }
}
