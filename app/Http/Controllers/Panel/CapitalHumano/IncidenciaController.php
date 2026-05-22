<?php

namespace App\Http\Controllers\Panel\CapitalHumano;

use App\Enums\EstadoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\IncidenciasFiltrosTrait;
use App\Http\Requests\CapitalHumano\AprobarIncidenciaRequest;
use App\Http\Requests\CapitalHumano\EnviarSindicatoIncidenciaRequest;
use App\Http\Requests\CapitalHumano\RechazarIncidenciaRequest;
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
        return Inertia::render('Panel/CapitalHumano/Incidencias/Index',
            $this->listadoIncidencias($request),
        );
    }

    public function show(Incidencia $incidencia): Response
    {
        return Inertia::render('Panel/CapitalHumano/Incidencias/Show', [
            'incidencia' => $this->incidenciaConRelaciones($incidencia),
        ]);
    }

    public function aprobar(AprobarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->aprobarCapitalHumano($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.capital_humano.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} aprobada y enviada a la Subdirección Administrativa.");
    }

    public function rechazar(RechazarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo, EstadoIncidencia::PendienteCapitalHumano);

        return redirect()
            ->route('panel.capital_humano.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} rechazada.");
    }

    public function enviarSindicato(EnviarSindicatoIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->enviarSindicato($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.capital_humano.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} enviada a Sindicato.");
    }
}
