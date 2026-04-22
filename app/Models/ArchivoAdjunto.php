<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArchivoAdjunto extends Model
{
    protected $table = 'archivos_adjuntos';

    const UPDATED_AT = null;

    protected $appends = [
        'url',
        'tamanio_legible',
    ];

    protected $fillable = [
        'incidencia_id',
        'nombre_original',
        'ruta_storage',
        'mime_type',
        'tamanio_bytes',
        'subido_por',
    ];

    protected function casts(): array
    {
        return [
            'tamanio_bytes' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->ruta_storage);
    }

    public function getUrlAttribute(): string
    {
        return $this->url();
    }

    public function tamanioLegible(): string
    {
        $bytes = $this->tamanio_bytes;
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }

    public function getTamanioLegibleAttribute(): string
    {
        return $this->tamanioLegible();
    }
}
