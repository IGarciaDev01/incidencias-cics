<?php

namespace App\Services;

use App\Enums\Prioridad;
use App\Models\Incidencia;
use App\Models\SlaConfiguracion;
use Carbon\CarbonImmutable;

class SlaService
{
    public function calcularFechaLimite(Prioridad $prioridad): CarbonImmutable
    {
        $sla = SlaConfiguracion::where('prioridad', $prioridad)
            ->where('activa', true)
            ->first();

        $horas = $sla?->horas_resolucion ?? $this->horasPorDefecto($prioridad);

        return now()->addHours($horas);
    }

    public function calcularFechaRespuesta(Prioridad $prioridad): CarbonImmutable
    {
        $sla = SlaConfiguracion::where('prioridad', $prioridad)
            ->where('activa', true)
            ->first();

        $horas = $sla?->horas_primera_respuesta ?? $this->horasRespuestaPorDefecto($prioridad);

        return now()->addHours($horas);
    }

    public function porcentajeTiempoTranscurrido(Incidencia $incidencia): ?int
    {
        return null;
    }

    private function horasPorDefecto(Prioridad $prioridad): int
    {
        return match ($prioridad) {
            Prioridad::Alta => 72,
            Prioridad::Media => 168,
            Prioridad::Baja => 336,
        };
    }

    private function horasRespuestaPorDefecto(Prioridad $prioridad): int
    {
        return match ($prioridad) {
            Prioridad::Alta => 24,
            Prioridad::Media => 72,
            Prioridad::Baja => 168,
        };
    }
}
