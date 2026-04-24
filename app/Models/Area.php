<?php

namespace App\Models;

use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'subdirector_id',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function subdirector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subdirector_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
}
