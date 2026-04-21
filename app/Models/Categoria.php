<?php

namespace App\Models;

use App\Enums\Prioridad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'prioridad_defecto',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'prioridad_defecto' => Prioridad::class,
            'activa'            => 'boolean',
        ];
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
}
