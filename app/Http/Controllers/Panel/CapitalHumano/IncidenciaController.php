<?php

namespace App\Http\Controllers\Panel\CapitalHumano;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Requests\CapitalHumano\AprobarIncidenciaRequest;
use App\Http\Requests\CapitalHumano\RechazarIncidenciaRequest;
use App\Models\Area;
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
        $query = Incidencia::with(['area:id,nombre'])
            ->when($request->filled('fecha_inicio'), fn ($q) => $q->whereDate('fecha_incidencia', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn ($q) => $q->whereDate('fecha_incidencia', '<=', $request->fecha_fin))
            ->when($request->string('estado')->isNotEmpty(), function ($q) use ($request) {
                $q->where('estado', $request->estado);
            })
            ->when($request->string('tipo')->isNotEmpty(), function ($q) use ($request) {
                $q->where('tipo_incidencia', $request->tipo);
            })
            ->when($request->filled('area_id'), fn ($q) => $q->where('area_id', $request->area_id))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q2) => $q2->where('folio', 'like', "%{$request->buscar}%")
                ->orWhere('numero_empleado', 'like', "%{$request->buscar}%")
                ->orWhere('reportante_nombre', 'like', "%{$request->buscar}%")
            )
            )
            ->latest();

        return Inertia::render('Panel/CapitalHumano/Incidencias/Index', [
            'incidencias' => $query->paginate(20)->withQueryString(),
            'filtros' => $request->only(['estado', 'tipo', 'buscar', 'area_id', 'fecha_inicio', 'fecha_fin']),
            'estados' => array_map(fn ($e) => ['value' => $e->value, 'name' => $e->name], EstadoIncidencia::cases()),
            'tipos' => array_map(fn ($t) => ['value' => $t->value, 'name' => $t->name], TipoIncidencia::cases()),
            'areas' => Area::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function show(Incidencia $incidencia): Response
    {
        $incidencia->load([
            'area:id,nombre',
            'revisadoPor:id,nombre',
            'historial.user:id,nombre',
            'archivos',
        ]);

        return Inertia::render('Panel/CapitalHumano/Incidencias/Show', [
            'incidencia' => $incidencia,
        ]);
    }

    public function aprobar(AprobarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->aprobarCapitalHumano($incidencia, $request->user(), $request->comentario);

        return redirect()
            ->route('panel.capital_humano.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} aprobada y enviada a la Subdirección Académica.");
    }

    public function rechazar(RechazarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->rechazar($incidencia, $request->user(), $request->motivo);

        return redirect()
            ->route('panel.capital_humano.incidencias.show', $incidencia)
            ->with('success', "Incidencia {$incidencia->folio} rechazada.");
    }
}
