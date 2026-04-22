<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidenciaPublicaRequest;
use App\Models\Area;
use App\Models\Empleado;
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

        $numero = trim((string) $request->numero);

        $resultados = Empleado::query()
            ->where('numero_empleado', 'like', "{$numero}%")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['numero_empleado', 'nombre', 'email', 'tipo'])
            ->map(fn (Empleado $e) => [
                // Mantener compatibilidad con el frontend existente.
                'numero_empleado' => $e->numero_empleado,
                'reportante_nombre' => $e->nombre,
                'email_reportante' => $e->email,
                'tipo_empleado' => $e->tipo?->value,
            ]);

        return response()->json($resultados->values());
    }

    public function confirmacion(string $folio): Response
    {
        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        return Inertia::render('Public/Incidencias/Confirmacion', [
            'folio' => $incidencia->folio,
            'token' => $incidencia->token_seguimiento,
            'numero_empleado' => $incidencia->numero_empleado,
            'tipo_incidencia' => $incidencia->tipo_incidencia->label(),
            'fecha_incidencia' => $incidencia->fecha_incidencia->format('d/m/Y'),
        ]);
    }
}
