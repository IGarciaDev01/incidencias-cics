<?php

namespace App\Enums;

enum TipoAccionHistorial: string
{
    case Creada         = 'creada';
    case Aprobada       = 'aprobada';
    case Rechazada      = 'rechazada';
    case Asignada       = 'asignada';
    case Reasignada     = 'reasignada';
    case EnProceso      = 'en_proceso';
    case Resuelta       = 'resuelta';
    case Cerrada        = 'cerrada';
    case Reabierta      = 'reabierta';
    case Comentario     = 'comentario';
    case SolicitudInfo  = 'solicitud_info';
    case ArchivoAdjunto = 'archivo_adjunto';

    public function label(): string
    {
        return match($this) {
            self::Creada         => 'Incidencia creada',
            self::Aprobada       => 'Incidencia aprobada',
            self::Rechazada      => 'Incidencia rechazada',
            self::Asignada       => 'Coordinador asignado',
            self::Reasignada     => 'Coordinador reasignado',
            self::EnProceso      => 'Incidencia en proceso',
            self::Resuelta       => 'Incidencia resuelta',
            self::Cerrada        => 'Incidencia cerrada',
            self::Reabierta      => 'Incidencia reabierta',
            self::Comentario     => 'Comentario agregado',
            self::SolicitudInfo  => 'Solicitud de información',
            self::ArchivoAdjunto => 'Archivo adjunto',
        };
    }
}
