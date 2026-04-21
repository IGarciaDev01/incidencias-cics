<?php

namespace App\Http\Controllers\Panel\Coordinador;

use App\Enums\EstadoIncidencia;
use App\Enums\Prioridad;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjuntarArchivoRequest;
use App\Http\Requests\ComentarIncidenciaRequest;
use App\Http\Requests\Coordinador\EscalarIncidenciaRequest;
use App\Http\Requests\Coordinador\ResolverIncidenciaRequest;
use App\Http\Requests\Coordinador\SolicitarInfoRequest;
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
        $user  = $request->user();

        $query = Incidencia::with(['categoria:id,nombre', 'area:id,nombre'])
            ->where('asignado_a', $user->id)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->prioridad))
            ->when($request->filled('buscar'), fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('folio', 'like', "%{$request->buscar}%")
                       ->orWhere('titulo', 'like', "%{$request->buscar}%")
                )
            )
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderByRaw("FIELD(estado, 'aprobada', 'en_proceso', 'resuelta', 'cerrada')")
            ->latest();

        return Inertia::render('Panel/Coordinador/Incidencias/Index', [
            'incidencias' => $query->paginate(20)->withQueryString(),
            'filtros'     => $request->only(['estado', 'prioridad', 'buscar']),
            'estados'     => EstadoIncidencia::cases(),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function show(Request $request, Incidencia $incidencia): Response
    {
        abort_if(
            $incidencia->asignado_a !== $request->user()->id,
            403,
            'No tienes acceso a esta incidencia.'
        );

        $incidencia->load([
            'categoria',
            'area',
            'user:id,nombre,email',
            'asignadoA:id,nombre',
            'revisadoPor:id,nombre',
            'historial.user:id,nombre',
            'archivos.subidoPor:id,nombre',
        ]);

        return Inertia::render('Panel/Coordinador/Incidencias/Show', [
            'incidencia' => $incidencia,
        ]);
    }

    public function iniciar(Request $request, Incidencia $incidencia): RedirectResponse
    {
        $this->incidenciaService->iniciarProceso($incidencia, $request->user());

        return back()->with('success', 'Incidencia marcada como en proceso.');
    }

    public function resolver(ResolverIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->asignado_a !== $request->user()->id, 403);

        $this->incidenciaService->resolver($incidencia, $request->user(), $request->resolucion);

        return redirect()->route('panel.coordinador.incidencias.show', $incidencia)
            ->with('success', 'Incidencia marcada como resuelta.');
    }

    public function cerrar(Request $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->asignado_a !== $request->user()->id, 403);

        $this->incidenciaService->cerrar($incidencia, $request->user());

        return redirect()->route('panel.coordinador.incidencias.index')
            ->with('success', "Incidencia {$incidencia->folio} cerrada.");
    }

    public function escalar(EscalarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->asignado_a !== $request->user()->id, 403);

        $this->incidenciaService->escalar($incidencia, $request->user(), $request->motivo);

        return redirect()->route('panel.coordinador.incidencias.index')
            ->with('success', 'Incidencia escalada a subdirección.');
    }

    public function comentar(ComentarIncidenciaRequest $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->asignado_a !== $request->user()->id, 403);

        $this->incidenciaService->comentar(
            incidencia: $incidencia,
            user: $request->user(),
            comentario: $request->comentario,
            esInterno: $request->boolean('es_interno'),
        );

        return back()->with('success', 'Comentario agregado.');
    }

    public function solicitarInformacion(SolicitarInfoRequest $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->asignado_a !== $request->user()->id, 403);

        $this->incidenciaService->solicitarInformacion($incidencia, $request->user(), $request->mensaje);

        return back()->with('success', 'Solicitud de información enviada al reportante.');
    }
}
