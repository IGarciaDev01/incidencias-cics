<?php

namespace App\Http\Controllers;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Http\Requests\LoginSeguimientoRequest;
use App\Models\Empleado;
use App\Models\Incidencia;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SeguimientoController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $numeroEmpleado = session('empleado_auth');

        if ($numeroEmpleado) {
            return redirect()->route('seguimiento.panel');
        }

        return Inertia::render('Public/Seguimiento/Index');
    }

    public function login(LoginSeguimientoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $empleado = Empleado::where('numero_empleado', $data['numero_empleado'])->first();

        if (! $empleado || ! $empleado->password || ! Hash::check($data['password'], $empleado->password)) {
            return back()->withErrors([
                'numero_empleado' => 'El número de empleado o la contraseña son incorrectos.',
            ])->onlyInput('numero_empleado');
        }

        $request->session()->put('empleado_auth', $empleado->numero_empleado);

        return redirect()->route('seguimiento.panel');
    }

    public function panel(Request $request): Response|RedirectResponse
    {
        $numeroEmpleado = session('empleado_auth');

        if (! $numeroEmpleado) {
            return redirect()->route('seguimiento.index');
        }

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        $query = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->with('area:id,nombre')
            ->withCount('archivos');

        if ($request->filled('fecha') && $request->filled('fecha_fin')) {
            $query->where('fecha_incidencia', '>=', Carbon::parse($request->fecha)->startOfDay())
                ->where('fecha_incidencia', '<=', Carbon::parse($request->fecha_fin)->endOfDay());
        } elseif ($request->filled('fecha')) {
            $fecha = Carbon::parse($request->fecha);
            $query->whereYear('fecha_incidencia', $fecha->year)
                ->whereMonth('fecha_incidencia', $fecha->month);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_incidencia', $request->tipo);
        }

        $incidencias = $query->orderByDesc('created_at')->paginate(15)->withQueryString()->through(fn ($i) => [
            'id' => $i->id,
            'folio' => $i->folio,
            'tipo_incidencia' => $i->tipo_incidencia->value,
            'fecha_incidencia' => $i->fecha_incidencia?->format('Y-m-d'),
            'hora_incidencia' => $i->hora_incidencia,
            'minutos_retardo' => $i->minutos_retardo,
            'estado' => $i->estado->value,
            'created_at' => $i->created_at->toISOString(),
            'area' => $i->area ? ['id' => $i->area->id, 'nombre' => $i->area->nombre] : null,
            'archivos_count' => $i->archivos_count,
        ]);

        $aprobadas = $incidencias->getCollection()->filter(fn ($i) => $i['estado'] === 'aprobada')->count();
        $rechazadas = $incidencias->getCollection()->filter(fn ($i) => $i['estado'] === 'rechazada')->count();
        $pendientes = $incidencias->getCollection()->filter(fn ($i) => ! in_array($i['estado'], ['aprobada', 'rechazada'], true))->count();

        return Inertia::render('Public/Seguimiento/Show', [
            'empleado' => [
                'numero_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre,
                'email' => $empleado->email,
                'tipo' => $empleado->tipo?->value,
            ],
            'incidencias' => $incidencias,
            'filtros' => $request->only(['fecha', 'fecha_fin', 'estado', 'tipo']),
            'estadisticas' => [
                'total' => $incidencias->total(),
                'pendientes' => $pendientes,
                'aprobadas' => $aprobadas,
                'rechazadas' => $rechazadas,
            ],
            'estados' => collect(EstadoIncidencia::cases())->map(fn ($e) => ['value' => $e->value, 'label' => $e->label(), 'color' => $e->color()]),
            'tipos' => collect(TipoIncidencia::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()]),
        ]);
    }

    public function show(Request $request, string $folio): Response|RedirectResponse
    {
        $numeroEmpleado = session('empleado_auth');

        if (! $numeroEmpleado) {
            return redirect()->route('seguimiento.index');
        }

        $incidencia = Incidencia::where('folio', $folio)
            ->where('numero_empleado', $numeroEmpleado)
            ->with([
                'area:id,nombre',
                'historial' => fn ($q) => $q->where('es_interno', false)->with('user:id,nombre'),
                'archivos',
            ])
            ->firstOrFail();

        return Inertia::render('Public/Seguimiento/Detalle', [
            'incidencia' => [
                'id' => $incidencia->id,
                'folio' => $incidencia->folio,
                'numero_empleado' => $incidencia->numero_empleado,
                'reportante_nombre' => $incidencia->reportante_nombre,
                'tipo_solicitante' => $incidencia->tipo_solicitante->value,
                'tipo_incidencia' => $incidencia->tipo_incidencia->value,
                'minutos_retardo' => $incidencia->minutos_retardo,
                'fecha_incidencia' => $incidencia->fecha_incidencia?->format('Y-m-d'),
                'hora_incidencia' => $incidencia->hora_incidencia,
                'descripcion' => $incidencia->descripcion,
                'estado' => $incidencia->estado->value,
                'motivo_rechazo' => $incidencia->motivo_rechazo,
                'created_at' => $incidencia->created_at->toISOString(),
                'area' => $incidencia->area ? ['id' => $incidencia->area->id, 'nombre' => $incidencia->area->nombre] : null,
                'historial' => $incidencia->historial->map(fn ($h) => [
                    'id' => $h->id,
                    'tipo_accion' => $h->tipo_accion->value,
                    'comentario' => $h->comentario,
                    'created_at' => $h->created_at->toISOString(),
                    'user' => $h->user ? ['id' => $h->user->id, 'nombre' => $h->user->nombre] : null,
                ]),
                'archivos' => $incidencia->archivos->map(fn ($a) => [
                    'id' => $a->id,
                    'nombre_original' => $a->nombre_original,
                    'mime_type' => $a->mime_type,
                    'tamanio_bytes' => $a->tamanio_bytes,
                    'created_at' => $a->created_at->toISOString(),
                ]),
            ],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('empleado_auth');

        return redirect()->route('seguimiento.index')->with('success', 'Sesión cerrada correctamente.');
    }
}
