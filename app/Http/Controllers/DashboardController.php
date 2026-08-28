<?php

namespace App\Http\Controllers;

use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = match ($user->rol) {
            RolUsuario::JefeInmediato => $this->statsJefe($user),
            RolUsuario::CapitalHumano => $this->statsCapitalHumano(),
            RolUsuario::Sindicato => $this->statsSindicato(),
            RolUsuario::Subdirector => $this->statsSubdirector(),
        };

        return Inertia::render('Panel/Dashboard', [
            'stats' => $stats,
            'rol' => $user->rol->value,
            'areaNombre' => $user->esJefeInmediato() ? $user->area?->nombre : null,
        ]);
    }

    private function statsSubdirector(): array
    {
        $total = Incidencia::count();
        $aprobadas = Incidencia::where('estado', EstadoIncidencia::Aprobada)->count();

        return [
            'pendientes' => Incidencia::where('estado', EstadoIncidencia::PendienteSubdireccion)->count(),
            'aprobadas' => $aprobadas,
            'rechazadas' => Incidencia::where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => $total,
            'tasa_aprobacion' => $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0,
            'charts' => [
                'por_estado' => $this->countPorEstado(),
                'por_tipo' => $this->countPorTipo(),
                'por_area' => $this->countPorArea(),
                'por_solicitante' => $this->countPorSolicitante(),
                'solicitudes_mes' => $this->solicitudesPorMes(),
            ],
        ];
    }

    private function statsCapitalHumano(): array
    {
        $estadosCapitalHumano = [
            EstadoIncidencia::PendienteCapitalHumano,
            EstadoIncidencia::PendienteSindicato,
            EstadoIncidencia::PendienteSubdireccion,
            EstadoIncidencia::Aprobada,
            EstadoIncidencia::Rechazada,
        ];

        $base = Incidencia::whereIn('estado', $estadosCapitalHumano);
        $total = (clone $base)->count();
        $aprobadas = (clone $base)->where('estado', EstadoIncidencia::Aprobada)->count();

        return [
            'pendientes' => (clone $base)->where('estado', EstadoIncidencia::PendienteCapitalHumano)->count(),
            'aprobadas' => $aprobadas,
            'rechazadas' => (clone $base)->where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => $total,
            'tasa_aprobacion' => $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0,
            'charts' => [
                'por_estado' => $this->countPorEstado(estados: $estadosCapitalHumano),
                'por_tipo' => $this->countPorTipo(estados: $estadosCapitalHumano),
                'por_area' => $this->countPorArea(estados: $estadosCapitalHumano),
                'solicitudes_mes' => $this->solicitudesPorMes(estados: $estadosCapitalHumano),
            ],
        ];
    }

    private function statsSindicato(): array
    {
        $base = Incidencia::query();
        $total = (clone $base)->count();
        $aprobadas = (clone $base)->where('estado', EstadoIncidencia::Aprobada)->count();

        return [
            'pendientes' => (clone $base)->whereNotIn('estado', [EstadoIncidencia::Aprobada, EstadoIncidencia::Rechazada])->count(),
            'aprobadas' => $aprobadas,
            'rechazadas' => (clone $base)->where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => $total,
            'tasa_aprobacion' => $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0,
            'charts' => [
                'por_estado' => $this->countPorEstado(),
                'por_tipo' => $this->countPorTipo(),
                'por_area' => $this->countPorArea(),
                'solicitudes_mes' => $this->solicitudesPorMes(),
            ],
        ];
    }

    private function statsJefe(User $jefe): array
    {
        $areaId = $jefe->area_id;

        abort_if(! $areaId, 403, 'No tienes un área asignada.');

        $base = Incidencia::where('area_id', $areaId);
        $total = (clone $base)->count();
        $aprobadas = (clone $base)->where('estado', EstadoIncidencia::Aprobada)->count();

        return [
            'pendientes' => (clone $base)->where('estado', EstadoIncidencia::PendienteJefe)->count(),
            'aprobadas' => $aprobadas,
            'rechazadas' => (clone $base)->where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => $total,
            'tasa_aprobacion' => $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0,
            'charts' => [
                'por_estado' => $this->countPorEstado($areaId),
                'por_tipo' => $this->countPorTipo($areaId),
                'por_solicitante' => $this->countPorSolicitante($areaId),
                'solicitudes_mes' => $this->solicitudesPorMes($areaId),
            ],
        ];
    }

    private function countPorEstado(?int $areaId = null, bool $enviadasSindicato = false, ?array $estados = null): array
    {
        $query = Incidencia::query();
        if ($enviadasSindicato) {
            $query->enviadasSindicato();
        }
        if ($areaId) {
            $query->where('area_id', $areaId);
        }
        if ($estados) {
            $query->whereIn('estado', $estados);
        }

        $estados = [
            'pendiente_jefe' => 0,
            'pendiente_capital_humano' => 0,
            'pendiente_sindicato' => 0,
            'pendiente_subdireccion' => 0,
            'aprobada' => 0,
            'rechazada' => 0,
        ];

        $result = (clone $query)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        foreach ($result as $estado => $count) {
            if (isset($estados[$estado])) {
                $estados[$estado] = $count;
            }
        }

        return $estados;
    }

    private function countPorTipo(?int $areaId = null, bool $enviadasSindicato = false, ?array $estados = null): array
    {
        $query = Incidencia::query();
        if ($enviadasSindicato) {
            $query->enviadasSindicato();
        }
        if ($areaId) {
            $query->where('area_id', $areaId);
        }
        if ($estados) {
            $query->whereIn('estado', $estados);
        }

        $tipos = [
            'retardo' => 0,
            'permiso_economico' => 0,
            'comision_oficial' => 0,
            'salida_anticipada' => 0,
            'permiso_sindical' => 0,
            'incidencia_medica' => 0,
            'buena_conducta' => 0,
        ];

        $result = (clone $query)
            ->select('tipo_incidencia', DB::raw('count(*) as total'))
            ->groupBy('tipo_incidencia')
            ->pluck('total', 'tipo_incidencia')
            ->toArray();

        foreach ($result as $tipo => $count) {
            if (isset($tipos[$tipo])) {
                $tipos[$tipo] = $count;
            }
        }

        return $tipos;
    }

    private function countPorArea(?int $areaId = null, bool $enviadasSindicato = false, ?array $estados = null): array
    {
        $query = Incidencia::query();
        if ($enviadasSindicato) {
            $query->enviadasSindicato();
        }
        if ($areaId) {
            $query->where('area_id', $areaId);
        }
        if ($estados) {
            $query->whereIn('estado', $estados);
        }

        $result = (clone $query)
            ->join('areas', 'incidencias.area_id', '=', 'areas.id')
            ->select('areas.nombre', DB::raw('count(*) as total'))
            ->groupBy('areas.id', 'areas.nombre')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'nombre')
            ->toArray();

        return $result;
    }

    private function countPorSolicitante(?int $areaId = null): array
    {
        $query = Incidencia::query();
        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        $result = (clone $query)
            ->select('tipo_solicitante', DB::raw('count(*) as total'))
            ->groupBy('tipo_solicitante')
            ->pluck('total', 'tipo_solicitante')
            ->toArray();

        return $result;
    }

    private function solicitudesPorMes(?int $areaId = null, int $months = 6, bool $enviadasSindicato = false, ?array $estados = null): array
    {
        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%%Y-%%m', fecha_incidencia)"
            : "DATE_FORMAT(fecha_incidencia, '%Y-%m')";

        $query = Incidencia::query()
            ->select(
                DB::raw("{$dateFormat} as mes"),
                DB::raw('count(*) as total')
            )
            ->where('fecha_incidencia', '>=', now()->timezone('America/Mexico_City')->subMonths($months)->startOfMonth());

        if ($enviadasSindicato) {
            $query->enviadasSindicato();
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        if ($estados) {
            $query->whereIn('estado', $estados);
        }

        $result = $query->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $meses = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $fecha = now()->timezone('America/Mexico_City')->subMonths($i);
            $mes = $fecha->format('Y-m');
            $label = rtrim($fecha->locale('es')->translatedFormat('M'), '.');
            $meses[$mes] = [
                'label' => ucfirst($label),
                'total' => $result[$mes] ?? 0,
            ];
        }

        return $meses;
    }
}
