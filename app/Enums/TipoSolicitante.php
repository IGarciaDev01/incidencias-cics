<?php

namespace App\Enums;

enum TipoSolicitante: string
{
    case Docente        = 'docente';
    case Administrativo = 'administrativo';

    public function label(): string
    {
        return match($this) {
            self::Docente        => 'Docente',
            self::Administrativo => 'Administrativo',
        };
    }
}
