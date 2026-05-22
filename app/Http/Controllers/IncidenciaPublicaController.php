<?php

namespace App\Http\Controllers;

use App\Exceptions\LimiteIncidenciaExcepcion;
use App\Http\Requests\StoreIncidenciaPublicaRequest;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Services\IncidenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class IncidenciaPublicaController extends Controller
{
    public function __construct(private readonly IncidenciaService $incidenciaService) {}

    public function create(): Response
    {
        return Inertia::render('Public/Incidencias/Create', [
            'areas' => Area::conJefeOperativo()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreIncidenciaPublicaRequest $request): RedirectResponse
    {
        try {
            $incidencia = $this->incidenciaService->crear($request->validated());
        } catch (LimiteIncidenciaExcepcion $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $this->incidenciaService->adjuntarArchivo($incidencia, $archivo);
            }
        }

        Session::put('seguimiento_verificado', $incidencia->folio);

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
                'numero_empleado' => $e->numero_empleado,
                'reportante_nombre' => $e->nombre,
                'email_reportante' => $e->email,
                'tipo_empleado' => $e->tipo?->value,
            ]);

        return response()->json($resultados->values());
    }

    public function confirmacion(string $folio): Response
    {
        abort_unless(Session::get('seguimiento_verificado') === $folio, 403, 'No tienes acceso a esta confirmación.');

        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        return Inertia::render('Public/Incidencias/Confirmacion', [
            'folio' => $incidencia->folio,
        ]);
    }
}
