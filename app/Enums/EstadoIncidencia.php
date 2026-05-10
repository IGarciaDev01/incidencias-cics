<?php

namespace App\Enums;

enum EstadoIncidencia: string
{
    case PendienteJefe = 'pendiente_jefe';
    case PendienteCapitalHumano = 'pendiente_capital_humano';
    case PendienteSubdireccion = 'pendiente_subdireccion';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';

    public function label(): string
    {
        return match ($this) {
            self::PendienteJefe => 'Pendiente (Jefe)',
            self::PendienteCapitalHumano => 'Pendiente (Capital Humano)',
            self::PendienteSubdireccion => 'Pendiente (Subdirección)',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendienteJefe => 'yellow',
            self::PendienteCapitalHumano => 'orange',
            self::PendienteSubdireccion => 'blue',
            self::Aprobada => 'green',
            self::Rechazada => 'red',
        };
    }

    public function esFinal(): bool
    {
        return in_array($this, [self::Aprobada, self::Rechazada], true);
    }

    public function visibleAlPublico(): bool
    {
        return true;
    }
}
