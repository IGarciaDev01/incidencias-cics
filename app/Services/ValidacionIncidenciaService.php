<?php

namespace App\Services;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Incidencia;
use Carbon\Carbon;

class ValidacionIncidenciaService
{
    public const MAX_RETARDOS_QUINCENALES = 2;

    public const MAX_PERMISO_ECONOMICO_MENSUALES = 3;

    public const MAX_PERMISO_ECONOMICO_ANUALES = 12;

    public function obtenerRazonRechazo(string $numeroEmpleado, TipoSolicitante $tipo, Carbon $fechaIncidencia, int $minutosRetardo, string $tipoIncidencia): ?string
    {
        if ($tipoIncidencia === TipoIncidencia::Retardo->value) {
            if ($minutosRetardo < 11 || $minutosRetardo >= 31) {
                return 'El retardo debe ser entre 11 y 30 minutos.';
            }

            if ($this->excedeLimiteQuincenalRetardos($numeroEmpleado, $fechaIncidencia)) {
                return 'Se ha alcanzado el límite máximo de 2 retardos por quincena.';
            }
        }

        if ($tipoIncidencia === TipoIncidencia::PermisoEconomico->value) {
            if ($this->excedeLimitePermisoEconomicoMensual($numeroEmpleado, $fechaIncidencia)) {
                return 'Se ha alcanzado el límite mensual de '.self::MAX_PERMISO_ECONOMICO_MENSUALES.' permisos económicos.';
            }

            if ($this->excedeLimitePermisoEconomicoAnual($numeroEmpleado, $fechaIncidencia)) {
                return 'Se ha alcanzado el límite anual de '.self::MAX_PERMISO_ECONOMICO_ANUALES.' permisos económicos.';
            }
        }

        return null;
    }

    public function excedeLimiteQuincenalRetardos(string $numeroEmpleado, Carbon $fecha): bool
    {
        $inicioQuincena = $this->obtenerInicioQuincena($fecha)->format('Y-m-d');
        $finQuincena = $this->obtenerFinQuincena($fecha)->format('Y-m-d');

        $count = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->whereBetween('fecha_incidencia', [$inicioQuincena, $finQuincena])
            ->where('tipo_incidencia', TipoIncidencia::Retardo->value)
            ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
            ->count();

        return $count >= self::MAX_RETARDOS_QUINCENALES;
    }

    public function excedeLimitePermisoEconomicoMensual(string $numeroEmpleado, Carbon $fecha): bool
    {
        $inicioMes = $fecha->copy()->startOfMonth()->format('Y-m-d');
        $finMes = $fecha->copy()->endOfMonth()->format('Y-m-d');

        $count = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->whereBetween('fecha_incidencia', [$inicioMes, $finMes])
            ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
            ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
            ->count();

        return $count >= self::MAX_PERMISO_ECONOMICO_MENSUALES;
    }

    public function excedeLimitePermisoEconomicoAnual(string $numeroEmpleado, Carbon $fecha): bool
    {
        $inicioAnio = $fecha->copy()->startOfYear()->format('Y-m-d');
        $finAnio = $fecha->copy()->endOfYear()->format('Y-m-d');

        $count = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->whereBetween('fecha_incidencia', [$inicioAnio, $finAnio])
            ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
            ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
            ->count();

        return $count >= self::MAX_PERMISO_ECONOMICO_ANUALES;
    }

    private function obtenerInicioQuincena(Carbon $fecha): Carbon
    {
        if ($fecha->day <= 15) {
            return $fecha->copy()->startOfMonth();
        }

        return $fecha->copy()->day(16)->startOfDay();
    }

    private function obtenerFinQuincena(Carbon $fecha): Carbon
    {
        if ($fecha->day <= 15) {
            return $fecha->copy()->day(15)->endOfDay();
        }

        return $fecha->copy()->endOfMonth();
    }
}
