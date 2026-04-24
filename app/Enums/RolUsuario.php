<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Admin = 'admin';
    case JefeInmediato = 'jefe_inmediato';
    case CapitalHumano = 'capital_humano';
    case Subdirector = 'subdirector';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::JefeInmediato => 'Jefe de Área',
            self::CapitalHumano => 'Capital Humano',
            self::Subdirector => 'Subdirector',
        };
    }
}
