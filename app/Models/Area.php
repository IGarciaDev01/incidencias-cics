<?php

namespace App\Models;

use App\Enums\RolUsuario;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'jefe_id',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'area_user')
            ->withPivot('es_jefe')
            ->withTimestamps();
    }

    public function jefes(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('es_jefe', true);
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }

    public function scopeConJefeOperativo(Builder $query): Builder
    {
        return $query
            ->where('activa', true)
            ->whereNotNull('jefe_id')
            ->whereHas('jefe', fn (Builder $q) => $q
                ->where('rol', RolUsuario::JefeInmediato)
                ->where('activo', true)
            )
            ->whereExists(fn ($q) => $q->from('area_user')
                ->whereColumn('area_user.area_id', 'areas.id')
                ->whereColumn('area_user.user_id', 'areas.jefe_id')
                ->where('area_user.es_jefe', true)
            );
    }
}
