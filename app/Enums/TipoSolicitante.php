<?php

namespace App\Enums;

enum TipoSolicitante: string
{
    case Docente = 'docente';
    case Administrativo = 'administrativo';
    case Paae = 'paae';

    public function label(): string
    {
        return match ($this) {
            self::Docente => 'Docente',
            self::Administrativo => 'Administrativo',
            self::Paae => 'PAAE',
        };
    }
}
