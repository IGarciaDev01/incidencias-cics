<?php

namespace App\Services;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoAccionHistorial;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Incidencia;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ValidacionIncidenciaService
{
    public const LIMITE_MENSUAL_DOCENTE = 12;

    public const LIMITE_MENSUAL_ADMINISTRATIVO = 12;

    public const MAX_MINUTOS_QUINCENALES = 120;

    public function validarYRechazar(string $numeroEmpleado, TipoSolicitante $tipo, Carbon $fechaIncidencia, int $minutosRetardo): ?Incidencia
    {
        $razon = $this->obtenerRazonRechazo($numeroEmpleado, $tipo, $fechaIncidencia, $minutosRetardo);

        if ($razon === null) {
            return null;
        }

        return $this->crearIncidenciaRechazada($numeroEmpleado, $tipo, $fechaIncidencia, $razon);
    }

    public function obtenerRazonRechazo(string $numeroEmpleado, TipoSolicitante $tipo, Carbon $fechaIncidencia, int $minutosRetardo): ?string
    {
        if ($this->excedeLimiteMensual($numeroEmpleado, $tipo, $fechaIncidencia)) {
            $limite = $this->obtenerLimiteMensual($tipo);

            return "Se ha alcanzado el límite mensual de {$limite} incidencias para {$tipo->label()}.";
        }

        if ($minutosRetardo > 0 && $this->excedeLimiteQuincenalRetardos($numeroEmpleado, $fechaIncidencia, $minutosRetardo)) {
            return 'Se ha alcanzado el límite máximo de 2 horas de retardo por quincena.';
        }

        return null;
    }

    public function excedeLimiteMensual(string $numeroEmpleado, TipoSolicitante $tipo, Carbon $fecha): bool
    {
        $limite = $this->obtenerLimiteMensual($tipo);
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();

        $count = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->whereBetween('fecha_incidencia', [$inicioMes, $finMes])
            ->whereIn('estado', [
                EstadoIncidencia::PendienteJefe->value,
                EstadoIncidencia::PendienteCapitalHumano->value,
                EstadoIncidencia::PendienteSubdireccion->value,
                EstadoIncidencia::Aprobada->value,
            ])
            ->count();

        return $count >= $limite;
    }

    public function excedeLimiteQuincenalRetardos(string $numeroEmpleado, Carbon $fecha, int $minutosRetardo): bool
    {
        $inicioQuincena = $this->obtenerInicioQuincena($fecha);
        $finQuincena = $this->obtenerFinQuincena($fecha);

        $totalMinutos = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->whereBetween('fecha_incidencia', [$inicioQuincena, $finQuincena])
            ->where('tipo_incidencia', TipoIncidencia::Retardo)
            ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
            ->sum('minutos_retardo');

        return ($totalMinutos + $minutosRetardo) > self::MAX_MINUTOS_QUINCENALES;
    }

    private function obtenerInicioQuincena(Carbon $fecha): Carbon
    {
        $day = $fecha->day;
        if ($day <= 15) {
            return $fecha->copy()->startOfMonth();
        }

        return $fecha->copy()->day(16)->startOfDay();
    }

    private function obtenerFinQuincena(Carbon $fecha): Carbon
    {
        $day = $fecha->day;
        if ($day <= 15) {
            return $fecha->copy()->day(15)->endOfDay();
        }

        return $fecha->copy()->endOfMonth();
    }

    private function obtenerLimiteMensual(TipoSolicitante $tipo): int
    {
        return match ($tipo) {
            TipoSolicitante::Docente => self::LIMITE_MENSUAL_DOCENTE,
            TipoSolicitante::Administrativo => self::LIMITE_MENSUAL_ADMINISTRATIVO,
        };
    }

    private function crearIncidenciaRechazada(string $numeroEmpleado, TipoSolicitante $tipo, Carbon $fechaIncidencia, string $razon): Incidencia
    {
        $incidencia = Incidencia::create([
            'folio' => app(FolioService::class)->generar(),
            'numero_empleado' => $numeroEmpleado,
            'reportante_nombre' => 'Sistema automático',
            'email_reportante' => null,
            'tipo_solicitante' => $tipo,
            'area_id' => null,
            'fecha_incidencia' => $fechaIncidencia->toDateString(),
            'tipo_incidencia' => TipoIncidencia::Retardo,
            'minutos_retardo' => 0,
            'descripcion' => $razon,
            'estado' => EstadoIncidencia::Rechazada,
            'token_seguimiento' => Str::random(64),
            'motivo_rechazo' => $razon,
        ]);

        app(HistorialService::class)->registrar(
            incidencia: $incidencia,
            tipo: TipoAccionHistorial::Rechazada,
            comentario: $razon,
        );

        return $incidencia;
    }
}
