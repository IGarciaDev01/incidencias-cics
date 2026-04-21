<?php

namespace App\Models;

use App\Enums\TipoAccionHistorial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialIncidencia extends Model
{
    // Solo tiene created_at, sin updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'incidencia_id',
        'user_id',
        'tipo_accion',
        'comentario',
        'es_interno',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tipo_accion' => TipoAccionHistorial::class,
            'es_interno'  => 'boolean',
            'metadata'    => 'array',
            'created_at'  => 'datetime',
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
}
