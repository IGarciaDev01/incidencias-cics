<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjuntarArchivoRequest;
use App\Http\Requests\BuscarSeguimientoRequest;
use App\Models\Incidencia;
use App\Services\IncidenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeguimientoController extends Controller
{
    public function __construct(private readonly IncidenciaService $incidenciaService) {}

    public function index(): Response
    {
        return Inertia::render('Public/Seguimiento/Index');
    }

    public function buscar(BuscarSeguimientoRequest $request): RedirectResponse
    {
        $incidencia = Incidencia::where('folio', $request->folio)
            ->when($request->filled('token'), fn ($q) => $q->where('token_seguimiento', $request->token))
            ->first();

        if (!$incidencia) {
            return back()->withErrors([
                'folio' => 'No se encontró ninguna incidencia con los datos proporcionados.',
            ])->onlyInput('folio');
        }

        $request->session()->put("seguimiento.{$incidencia->folio}", true);

        return redirect()->route('seguimiento.show', $incidencia->folio);
    }

    public function show(Request $request, string $folio): Response|RedirectResponse
    {
        $incidencia = Incidencia::where('folio', $folio)
            ->with([
                'area:id,nombre',
                'historial' => fn ($q) => $q->where('es_interno', false)->with('user:id,nombre'),
                'archivos',
            ])
            ->firstOrFail();

        $esPropietario   = $request->user() && $request->user()->id === $incidencia->user_id;
        $folioVerificado = $request->session()->has("seguimiento.{$folio}");

        if (!$esPropietario && !$folioVerificado) {
            return redirect()->route('seguimiento.index')
                ->withErrors(['folio' => 'Debes verificar el folio antes de ver los detalles.']);
        }

        return Inertia::render('Public/Seguimiento/Show', [
            'incidencia'  => $incidencia,
            'puedeActuar' => !$incidencia->estado->esFinal(),
        ]);
    }

    public function comentar(Request $request, string $folio): RedirectResponse
    {
        $incidencia = $this->resolverIncidenciaVerificada($request, $folio);
        $request->validate(['comentario' => ['required', 'string', 'min:10', 'max:2000']]);

        abort_if($incidencia->estado->esFinal(), 422, 'No se pueden agregar comentarios a incidencias finalizadas.');

        $this->incidenciaService->comentar($incidencia, $request->user(), $request->comentario);

        return back()->with('success', 'Comentario agregado correctamente.');
    }

    public function adjuntar(AdjuntarArchivoRequest $request, string $folio): RedirectResponse
    {
        $incidencia = $this->resolverIncidenciaVerificada($request, $folio);

        abort_if($incidencia->estado->esFinal(), 422, 'No se pueden adjuntar archivos a incidencias finalizadas.');

        $this->incidenciaService->adjuntarArchivo($incidencia, $request->file('archivo'), $request->user());

        return back()->with('success', 'Archivo adjuntado correctamente.');
    }

    private function resolverIncidenciaVerificada(Request $request, string $folio): Incidencia
    {
        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        $esPropietario   = $request->user() && $request->user()->id === $incidencia->user_id;
        $folioVerificado = $request->session()->has("seguimiento.{$folio}");

        abort_if(!$esPropietario && !$folioVerificado, 403, 'No tienes acceso a esta incidencia.');

        return $incidencia;
    }
}
