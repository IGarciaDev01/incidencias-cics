<?php

namespace App\Http\Controllers\Panel\JefeInmediato;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Requests\ComentarIncidenciaRequest;
use App\Http\Requests\JefeInmediato\AprobarIncidenciaRequest;
use App\Http\Requests\JefeInmediato\RechazarIncidenciaRequest;
use App\Models\Incidencia;
use App\Services\IncidenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidenciaController extends Controller
{
    public function __construct(private readonly IncidenciaService $incidenciaService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_if(! $user->area_id, 403, 'No tienes un área asignada.');

        $query = Incidencia::with(['area:id,nombre'])
            ->where('area_id', $user->area_id)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo_incidencia', $request->tipo))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q2) => $q2->where('folio', 'like', "%{$request->buscar}%")
                ->orWhere('numero_empleado', 'like', "%{$request->buscar}%")
                ->orWhere('reportante_nombre', 'like', "%{$request->buscar}%")
            )
            )
            ->latest();

        return Inertia::render('Panel/JefeInmediato/Incidencias/Index', [
            'incidencias' => $query->paginate(20)->withQueryString(),
            'filtros' => $request->only(['estado', 'tipo', 'buscar']),
            'estados' => EstadoIncidencia::cases(),
            'tipos' => TipoIncidencia::cases(),
        ]);
    }

    public function show(Request $request, Incidencia $incidencia): Response
    {
        $this->authorizeArea($request, $incidencia);

        $incidencia->load([
            'area:id,nombre',
            'revisadoPor:id,nombre',
            'historial.user:id,nombre',
            'archivos',
        ]);

        return Inertia::render('Panel/JefeInmediato/Incidencias/Show', [
            'incidencia' => $incidencia,
        ]);
    }

    public function aprobar(AprobarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->authorizeArea($request, $incidencia);

        $this->incidenciaService->aprobarJefe($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.jefe_inmediato.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} aprobada y enviada a Capital Humano.");
    }

    public function rechazar(RechazarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->authorizeArea($request, $incidencia);

        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo);

        return redirect()
            ->route('panel.jefe_inmediato.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} rechazada.");
    }

    public function comentar(ComentarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->authorizeArea($request, $incidencia);

        $this->incidenciaService->comentar(
            incidencia: $incidencia,
            user: $request->user(),
            comentario: $request->comentario,
            esInterno: (bool) $request->boolean('es_interno'),
        );

        return back()->with('success', 'Comentario agregado.');
    }

    private function authorizeArea(Request $request, Incidencia $incidencia): void
    {
        $user = $request->user();

        abort_if(! $user || ! $user->area_id, 403, 'No tienes un área asignada.');
        abort_if((int) $incidencia->area_id !== (int) $user->area_id, 403, 'No tienes permiso para acceder a esta incidencia.');
    }
}
