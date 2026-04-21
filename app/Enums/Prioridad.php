<?php

namespace App\Enums;

enum Prioridad: string
{
    case Alta  = 'alta';
    case Media = 'media';
    case Baja  = 'baja';

    public function label(): string
    {
        return match($this) {
            self::Alta  => 'Alta',
            self::Media => 'Media',
            self::Baja  => 'Baja',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Alta  => 'red',
            self::Media => 'yellow',
            self::Baja  => 'green',
        };
    }
}
