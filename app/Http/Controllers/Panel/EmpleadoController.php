<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmpleadoController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $buscar = $request->string('buscar')->trim();

        $incidenciasBase = Incidencia::query()
            ->whereColumn('incidencias.numero_empleado', 'empleados.numero_empleado');

        if ($user->esJefeInmediato()) {
            abort_if(! $user->area_id, 403, 'No tienes un área asignada.');

            $incidenciasBase->where('area_id', $user->area_id);
        }

        $empleados = Empleado::query()
            ->selectRaw('numero_empleado, nombre as reportante_nombre, email as email_reportante')
            ->when($buscar->isNotEmpty(), function ($q) use ($buscar) {
                $q->where(function ($q2) use ($buscar) {
                    $q2->where('numero_empleado', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%");
                });
            })
            ->addSelect([
                'total_incidencias' => (clone $incidenciasBase)->selectRaw('COUNT(*)'),
                'ultima_incidencia' => (clone $incidenciasBase)->selectRaw('MAX(created_at)'),
            ])
            ->when(
                $user->esJefeInmediato(),
                fn ($q) => $q->whereExists((clone $incidenciasBase)->selectRaw('1'))
            )
            ->orderByDesc('ultima_incidencia')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Panel/Empleados/Index', [
            'empleados' => $empleados,
            'filtros' => ['buscar' => (string) $buscar],
        ]);
    }

    public function show(Request $request, string $numeroEmpleado): Response
    {
        $user = $request->user();
        $empleadoModel = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();
        $query = Incidencia::where('numero_empleado', $numeroEmpleado);

        if ($user->esJefeInmediato()) {
            abort_if(! $user->area_id, 403, 'No tienes un área asignada.');

            $query->where('area_id', $user->area_id);
        }

        $incidencias = $query
            ->with('area:id,nombre')
            ->orderByDesc('created_at')
            ->get();

        if ($user->esJefeInmediato()) {
            abort_if($incidencias->isEmpty(), 404, 'Empleado no encontrado.');
        }

        $empleado = [
            'numero_empleado' => $numeroEmpleado,
            'reportante_nombre' => $empleadoModel->nombre,
            'email_reportante' => $empleadoModel->email,
        ];

        return Inertia::render('Panel/Empleados/Show', [
            'empleado' => $empleado,
            'incidencias' => $incidencias,
        ]);
    }
}
