<?php

namespace App\Enums;

enum RolUsuario: string
{
    case JefeInmediato = 'jefe_inmediato';
    case CapitalHumano = 'capital_humano';
    case Sindicato = 'sindicato';
    case Subdirector = 'subdirector';

    public function label(): string
    {
        return match ($this) {
            self::JefeInmediato => 'Jefe de Área',
            self::CapitalHumano => 'Capital Humano',
            self::Sindicato => 'Sindicato',
            self::Subdirector => 'Subdirección Administrativa',
        };
    }
}
