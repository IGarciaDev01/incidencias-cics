<?php

namespace App\Enums;

enum TipoIncidencia: string
{
    case Retardo          = 'retardo';
    case PermisoEconomico = 'permiso_economico';
    case ComisionOficial  = 'comision_oficial';
    case SalidaAnticipada = 'salida_anticipada';

    public function label(): string
    {
        return match($this) {
            self::Retardo          => 'Retardo',
            self::PermisoEconomico => 'Permiso Económico',
            self::ComisionOficial  => 'Comisión Oficial',
            self::SalidaAnticipada => 'Salida Anticipada',
        };
    }

    public function descripcion(): string
    {
        return match($this) {
            self::Retardo          => 'Retardo por n minutos (menor a 30 min, máximo 2 a la quincena)',
            self::PermisoEconomico => 'Permiso Económico',
            self::ComisionOficial  => 'Comisión Oficial',
            self::SalidaAnticipada => 'Salida Anticipada (Exclusivo personal PAAE)',
        };
    }

    public function requiereMinutos(): bool
    {
        return $this === self::Retardo;
    }

    public function soloParaPAAE(): bool
    {
        return $this === self::SalidaAnticipada;
    }
}
