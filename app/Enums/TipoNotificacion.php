<?php

namespace App\Enums;

enum TipoNotificacion: string
{
    case ConfirmacionRegistro = 'confirmacion_registro';
    case Asignacion = 'asignacion';
    case CambioEstado = 'cambio_estado';
    case AlertaSla = 'alerta_sla';
    case SolicitudInfo = 'solicitud_info';
    case ResolucionFinal = 'resolucion_final';

    public function asunto(string $folio): string
    {
        return match ($this) {
            self::ConfirmacionRegistro => "Incidencia {$folio} registrada correctamente",
            self::Asignacion => "Se te ha asignado la incidencia {$folio}",
            self::CambioEstado => "Actualización en tu incidencia {$folio}",
            self::AlertaSla => "ALERTA: La incidencia {$folio} está próxima a vencer",
            self::SolicitudInfo => "Solicitud de información adicional – incidencia {$folio}",
            self::ResolucionFinal => "Resolución final – incidencia {$folio}",
        };
    }
}
