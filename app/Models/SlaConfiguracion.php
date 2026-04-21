<?php

namespace App\Models;

use App\Enums\Prioridad;
use Illuminate\Database\Eloquent\Model;

class SlaConfiguracion extends Model
{
    protected $table = 'sla_configuracion';

    const CREATED_AT = null;

    protected $fillable = [
        'prioridad',
        'horas_primera_respuesta',
        'horas_resolucion',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'prioridad'               => Prioridad::class,
            'horas_primera_respuesta' => 'integer',
            'horas_resolucion'        => 'integer',
            'activa'                  => 'boolean',
            'updated_at'              => 'datetime',
        ];
    }
}
