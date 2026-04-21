<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Admin                = 'admin';
    case JefeInmediato        = 'jefe_inmediato';
    case CapitalHumano        = 'capital_humano';
    case SubdireccionAcademica = 'subdireccion_academica';

    public function label(): string
    {
        return match($this) {
            self::Admin                => 'Administrador',
            self::JefeInmediato        => 'Jefe Inmediato',
            self::CapitalHumano        => 'Capital Humano',
            self::SubdireccionAcademica => 'Subdirección Académica',
        };
    }
}
