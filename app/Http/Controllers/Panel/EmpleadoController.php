<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Models\Empleado;
use App\Models\Incidencia;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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
            abort_if(! $user->tieneArea(), 403, 'No tienes un área asignada.');
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

        $query = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->with('area:id,nombre');

        if ($user->esJefeInmediato()) {
            abort_if(! $user->tieneArea(), 403, 'No tienes un área asignada.');
            $query->where('area_id', $user->area_id);
        }

        if ($request->filled('fecha') && $request->filled('fecha_fin')) {
            $query->where('fecha_incidencia', '>=', $request->fecha)
                ->where('fecha_incidencia', '<=', $request->fecha_fin);
        } elseif ($request->filled('fecha')) {
            $fecha = Carbon::parse($request->fecha);
            $inicio = $fecha->copy()->startOfMonth();
            $fin = $fecha->copy()->endOfMonth();
            $query->where('fecha_incidencia', '>=', $inicio)
                ->where('fecha_incidencia', '<=', $fin);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_incidencia', $request->tipo);
        }

        $incidencias = $query->orderByDesc('created_at')->get();

        if ($user->esJefeInmediato()) {
            $existeEnArea = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->where('area_id', $user->area_id)
                ->exists();

            if (! $existeEnArea) {
                abort(403, 'No tienes permiso para acceder a este empleado.');
            }
        }

        $permisoEconomicoStats = null;

        if (! $user->esJefeInmediato()) {
            if ($request->filled('fecha') && $request->filled('fecha_fin')) {
                $inicio = Carbon::parse($request->fecha)->startOfDay();
                $fin = Carbon::parse($request->fecha_fin)->endOfDay();
            } elseif ($request->filled('fecha')) {
                $fecha = Carbon::parse($request->fecha);
                $inicio = $fecha->copy()->startOfMonth();
                $fin = $fecha->copy()->endOfMonth();
            } else {
                $inicio = Carbon::now()->startOfMonth();
                $fin = Carbon::now()->endOfMonth();
            }

            $permisoEconomicoMensualUsados = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->whereBetween('fecha_incidencia', [$inicio, $fin])
                ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
                ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
                ->count();

            $inicioAnio = Carbon::now()->startOfYear();
            $finAnio = Carbon::now()->endOfYear();

            $permisoEconomicoAnualUsados = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->whereBetween('fecha_incidencia', [$inicioAnio, $finAnio])
                ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
                ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
                ->count();

            $permisoEconomicoStats = [
                'mensual' => [
                    'usados' => $permisoEconomicoMensualUsados,
                    'disponibles' => max(0, 3 - $permisoEconomicoMensualUsados),
                    'total' => 3,
                ],
                'anual' => [
                    'usados' => $permisoEconomicoAnualUsados,
                    'disponibles' => max(0, 12 - $permisoEconomicoAnualUsados),
                    'total' => 12,
                ],
            ];
        }

        $empleado = [
            'numero_empleado' => $numeroEmpleado,
            'reportante_nombre' => $empleadoModel->nombre,
            'email_reportante' => $empleadoModel->email,
            'tipo' => $empleadoModel->tipo?->value,
        ];

        return Inertia::render('Panel/Empleados/Show', [
            'empleado' => $empleado,
            'incidencias' => $incidencias,
            'filtros' => $request->only(['fecha', 'fecha_fin', 'estado', 'tipo']),
            'estados' => array_map(fn ($e) => ['value' => $e->value, 'name' => $e->name], EstadoIncidencia::cases()),
            'tipos' => array_map(fn ($t) => ['value' => $t->value, 'name' => $t->name], TipoIncidencia::cases()),
            'permiso_economico_stats' => $permisoEconomicoStats,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeCreate($request->user());

        return Inertia::render('Panel/Empleados/Create');
    }

    public function store(StoreEmpleadoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Empleado::create($data);

        $rol = $request->user()->rol->value;

        $route = match ($rol) {
            'capital_humano' => 'panel.capital_humano.empleados.index',
            default => 'panel.subdireccion.empleados.index',
        };

        return redirect()->route($route)->with('success', 'Empleado creado correctamente.');
    }

    public function edit(Request $request, string $numeroEmpleado): Response
    {
        $this->authorizeCreate($request->user());

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        return Inertia::render('Panel/Empleados/Edit', [
            'empleado' => [
                'numero_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre,
                'email' => $empleado->email,
                'tipo' => $empleado->tipo?->value,
            ],
        ]);
    }

    public function update(StoreEmpleadoRequest $request, string $numeroEmpleado): RedirectResponse
    {
        $data = $request->validated();

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $empleado->update($data);

        $rol = $request->user()->rol->value;

        $route = match ($rol) {
            'capital_humano' => 'panel.capital_humano.empleados.index',
            default => 'panel.subdireccion.empleados.index',
        };

        return redirect()->route($route)->with('success', 'Empleado actualizado correctamente.');
    }

    private function authorizeCreate($user): void
    {
        abort_unless($user && ($user->esCapitalHumano() || $user->esSubdirector()), 403, 'No tienes permiso para realizar esta acción.');
    }
}
