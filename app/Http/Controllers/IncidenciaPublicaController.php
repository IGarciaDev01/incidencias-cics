<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidenciaPublicaRequest;
use App\Models\Area;
use App\Models\Incidencia;
use App\Services\IncidenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidenciaPublicaController extends Controller
{
    public function __construct(private readonly IncidenciaService $incidenciaService) {}

    public function create(): Response
    {
        return Inertia::render('Public/Incidencias/Create', [
            'areas' => Area::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreIncidenciaPublicaRequest $request): RedirectResponse
    {
        $incidencia = $this->incidenciaService->crear($request->validated());

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $this->incidenciaService->adjuntarArchivo($incidencia, $archivo);
            }
        }

        return redirect()->route('incidencias.confirmacion', $incidencia->folio);
    }

    public function buscarEmpleado(Request $request): JsonResponse
    {
        $request->validate(['numero' => ['required', 'string', 'max:20']]);

        $resultados = Incidencia::where('numero_empleado', $request->numero)
            ->select('reportante_nombre', 'email_reportante')
            ->distinct()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json($resultados->values());
    }

    public function confirmacion(string $folio): Response
    {
        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        return Inertia::render('Public/Incidencias/Confirmacion', [
            'folio'            => $incidencia->folio,
            'token'            => $incidencia->token_seguimiento,
            'numero_empleado'  => $incidencia->numero_empleado,
            'tipo_incidencia'  => $incidencia->tipo_incidencia->label(),
            'fecha_incidencia' => $incidencia->fecha_incidencia->format('d/m/Y'),
        ]);
    }
}
