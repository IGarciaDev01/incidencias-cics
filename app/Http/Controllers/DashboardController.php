<?php

namespace App\Http\Controllers;

use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;
use App\Models\Area;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = match ($user->rol) {
            RolUsuario::Admin => $this->statsAdmin(),
            RolUsuario::JefeInmediato => $this->statsJefe($user),
            RolUsuario::CapitalHumano => $this->statsCapitalHumano(),
            RolUsuario::Subdirector => $this->statsSubdirector(),
        };

        return Inertia::render('Panel/Dashboard', [
            'stats' => $stats,
            'rol' => $user->rol->value,
        ]);
    }

    private function statsAdmin(): array
    {
        return [
            'total_incidencias' => Incidencia::count(),
            'pendientes_jefe' => Incidencia::where('estado', EstadoIncidencia::PendienteJefe)->count(),
            'pendientes_capital_humano' => Incidencia::where('estado', EstadoIncidencia::PendienteCapitalHumano)->count(),
            'pendientes_subdireccion' => Incidencia::where('estado', EstadoIncidencia::PendienteSubdireccion)->count(),
            'aprobadas' => Incidencia::where('estado', EstadoIncidencia::Aprobada)->count(),
            'rechazadas' => Incidencia::where('estado', EstadoIncidencia::Rechazada)->count(),
            'total_usuarios' => User::where('activo', true)->count(),
            'total_areas' => Area::where('activa', true)->count(),
        ];
    }

    private function statsJefe(User $jefe): array
    {
        $base = Incidencia::when($jefe->area_id, fn ($q) => $q->where('area_id', $jefe->area_id));

        return [
            'pendientes' => (clone $base)->where('estado', EstadoIncidencia::PendienteJefe)->count(),
            'aprobadas' => (clone $base)->where('estado', EstadoIncidencia::Aprobada)->count(),
            'rechazadas' => (clone $base)->where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => (clone $base)->count(),
        ];
    }

    private function statsCapitalHumano(): array
    {
        return [
            'pendientes' => Incidencia::where('estado', EstadoIncidencia::PendienteCapitalHumano)->count(),
            'aprobadas' => Incidencia::where('estado', EstadoIncidencia::Aprobada)->count(),
            'rechazadas' => Incidencia::where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => Incidencia::whereIn('estado', [
                EstadoIncidencia::PendienteCapitalHumano,
                EstadoIncidencia::PendienteSubdireccion,
                EstadoIncidencia::Aprobada,
            ])->count(),
        ];
    }

    private function statsSubdirector(): array
    {
        return [
            'pendientes' => Incidencia::where('estado', EstadoIncidencia::PendienteSubdireccion)->count(),
            'aprobadas' => Incidencia::where('estado', EstadoIncidencia::Aprobada)->count(),
            'rechazadas' => Incidencia::where('estado', EstadoIncidencia::Rechazada)->count(),
            'total' => Incidencia::whereIn('estado', [
                EstadoIncidencia::PendienteSubdireccion,
                EstadoIncidencia::Aprobada,
                EstadoIncidencia::Rechazada,
            ])->count(),
        ];
    }
}
