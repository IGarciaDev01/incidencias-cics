<?php

namespace App\Http\Controllers\Panel\Sindicato;

use App\Enums\EstadoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\IncidenciasFiltrosTrait;
use App\Http\Requests\Sindicato\AprobarIncidenciaRequest;
use App\Http\Requests\Sindicato\RechazarIncidenciaRequest;
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
        return Inertia::render('Panel/Sindicato/Incidencias/Index',
            $this->listadoIncidencias($request, queryCallback: fn ($query) => $query->enviadasSindicato()),
        );
    }

    public function show(Incidencia $incidencia): Response
    {
        abort_if(! $incidencia->enviado_sindicato_at, 403);

        return Inertia::render('Panel/Sindicato/Incidencias/Show', [
            'incidencia' => $this->incidenciaConRelaciones($incidencia),
        ]);
    }

    public function aprobar(AprobarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->aprobarSindicato($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.sindicato.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} aprobada definitivamente por Sindicato.");
    }

    public function rechazar(RechazarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo, EstadoIncidencia::PendienteSindicato);

        return redirect()
            ->route('panel.sindicato.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} rechazada por Sindicato.");
    }
}
