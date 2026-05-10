<?php

namespace App\Http\Controllers\Panel\Subdireccion;

use App\Enums\EstadoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\IncidenciasFiltrosTrait;
use App\Http\Requests\Subdireccion\AprobarIncidenciaRequest;
use App\Http\Requests\Subdireccion\RechazarIncidenciaRequest;
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
        return Inertia::render('Panel/Subdireccion/Incidencias/Index',
            $this->listadoIncidencias($request),
        );
    }

    public function show(Incidencia $incidencia): Response
    {
        return Inertia::render('Panel/Subdireccion/Incidencias/Show', [
            'incidencia' => $this->incidenciaConRelaciones($incidencia),
        ]);
    }

    public function aprobar(AprobarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->aprobarSubdireccion($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.subdireccion.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} aprobada definitivamente.");
    }

    public function rechazar(RechazarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo, EstadoIncidencia::PendienteSubdireccion);

        return redirect()
            ->route('panel.subdireccion.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} rechazada.");
    }
}
