<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmpleadoController extends Controller
{
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $query = Incidencia::query();

        if ($user->esJefeInmediato() && $user->area_id) {
            $query->where('area_id', $user->area_id);
        }

        $buscar = $request->string('buscar')->trim();

        if ($buscar->isNotEmpty()) {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_empleado', 'like', "%{$buscar}%")
                    ->orWhere('reportante_nombre', 'like', "%{$buscar}%");
            });
        }

        $empleados = $query
            ->select('numero_empleado', 'reportante_nombre', 'email_reportante')
            ->selectRaw('COUNT(*) as total_incidencias')
            ->selectRaw('MAX(created_at) as ultima_incidencia')
            ->groupBy('numero_empleado', 'reportante_nombre', 'email_reportante')
            ->orderByDesc('ultima_incidencia')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Panel/Empleados/Index', [
            'empleados' => $empleados,
            'filtros'   => ['buscar' => (string) $buscar],
        ]);
    }

    public function show(Request $request, string $numeroEmpleado): Response
    {
        $user  = $request->user();
        $query = Incidencia::where('numero_empleado', $numeroEmpleado);

        if ($user->esJefeInmediato() && $user->area_id) {
            $query->where('area_id', $user->area_id);
        }

        $incidencias = $query
            ->with('area:id,nombre')
            ->orderByDesc('created_at')
            ->get();

        abort_if($incidencias->isEmpty(), 404, 'Empleado no encontrado.');

        $empleado = [
            'numero_empleado'   => $numeroEmpleado,
            'reportante_nombre' => $incidencias->first()->reportante_nombre,
            'email_reportante'  => $incidencias->first()->email_reportante,
        ];

        return Inertia::render('Panel/Empleados/Show', [
            'empleado'    => $empleado,
            'incidencias' => $incidencias,
        ]);
    }
}
