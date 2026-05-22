<?php

namespace App\Http\Controllers\Panel\JefeInmediato;

use App\Enums\EstadoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\IncidenciasFiltrosTrait;
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
    use IncidenciasFiltrosTrait;

    public function __construct(private readonly IncidenciaService $incidenciaService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_if(! $user->tieneArea(), 403, 'No tienes un área asignada.');

        $queryAreaId = $request->query('area');
        $areaId = $queryAreaId ? (int) $queryAreaId : (int) $user->area_id;

        abort_if(! $user->isJefeOfArea($areaId), 403, 'No tienes permiso para acceder a esta área.');

        return Inertia::render('Panel/JefeInmediato/Incidencias/Index',
            $this->listadoIncidencias($request, $areaId),
        );
    }

    public function show(Request $request, Incidencia $incidencia): Response
    {
        $this->authorizeArea($request, $incidencia);

        return Inertia::render('Panel/JefeInmediato/Incidencias/Show', [
            'incidencia' => $this->incidenciaConRelaciones($incidencia),
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

        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo, EstadoIncidencia::PendienteJefe);

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

        abort_if(! $user || ! $user->tieneArea(), 403, 'No tienes un área asignada.');

        $areaId = (int) $user->area_id;
        abort_if((int) $incidencia->area_id !== $areaId, 403, 'No tienes permiso para acceder a esta incidencia.');
    }
}
