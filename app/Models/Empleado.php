<?php

namespace App\Models;

use App\Enums\TipoSolicitante;
use Database\Factories\EmpleadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    /** @use HasFactory<EmpleadoFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_empleado',
        'nombre',
        'email',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoSolicitante::class,
        ];
    }
}
