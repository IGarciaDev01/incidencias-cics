<?php

namespace App\Http\Controllers\Panel\Subdireccion;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(Request $request): Response
    {
        $desde = $request->filled('desde') ? $request->date('desde') : now()->startOfMonth();
        $hasta = $request->filled('hasta') ? $request->date('hasta') : now()->endOfMonth();

        $base = Incidencia::whereBetween('incidencias.created_at', [$desde->startOfDay(), $hasta->endOfDay()]);

        $user = $request->user();
        $view = $user && $user->esCapitalHumano()
            ? 'Panel/CapitalHumano/Reportes/Index'
            : 'Panel/Subdireccion/Reportes/Index';

        return Inertia::render($view, [
            'filtros' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],

            'porEstado' => (clone $base)
                ->selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado'),

            'porTipoIncidencia' => (clone $base)
                ->selectRaw('tipo_incidencia, count(*) as total')
                ->groupBy('tipo_incidencia')
                ->pluck('total', 'tipo_incidencia'),

            'porTipoSolicitante' => (clone $base)
                ->selectRaw('tipo_solicitante, count(*) as total')
                ->groupBy('tipo_solicitante')
                ->pluck('total', 'tipo_solicitante'),

            'porArea' => (clone $base)
                ->join('areas', 'incidencias.area_id', '=', 'areas.id')
                ->selectRaw('areas.nombre as area, count(*) as total')
                ->groupBy('areas.nombre')
                ->orderByDesc('total')
                ->pluck('total', 'area'),
        ]);
    }

    public function exportar(Request $request): HttpResponse
    {
        $desde = $request->filled('desde') ? $request->date('desde') : now()->startOfMonth();
        $hasta = $request->filled('hasta') ? $request->date('hasta') : now()->endOfMonth();

        $incidencias = Incidencia::with('area:id,nombre')
            ->whereBetween('created_at', [$desde->startOfDay(), $hasta->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        $csv = $this->generarCsv($incidencias);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"reporte-{$desde->toDateString()}-{$hasta->toDateString()}.csv\"",
        ]);
    }

    private function generarCsv($incidencias): string
    {
        $cabeceras = [
            'Folio',
            'No. Empleado',
            'Nombre',
            'Correo',
            'Tipo Solicitante',
            'Área',
            'Fecha Incidencia',
            'Tipo Incidencia',
            'Minutos Retardo',
            'Estado',
            'Registrada',
        ];

        $filas = $incidencias->map(fn ($i) => [
            $i->folio,
            $i->numero_empleado,
            $i->reportante_nombre,
            $i->email_reportante ?? '',
            $i->tipo_solicitante->value,
            $i->area?->nombre ?? 'Sin área',
            $i->fecha_incidencia->format('d/m/Y'),
            $i->tipo_incidencia->value,
            $i->minutos_retardo ?? '',
            $i->estado->value,
            $i->created_at->format('d/m/Y H:i'),
        ]);

        $output = implode(',', array_map(fn ($h) => '"'.$h.'"', $cabeceras))."\n";

        foreach ($filas as $fila) {
            $output .= implode(',', array_map(
                fn ($col) => '"'.str_replace('"', '""', (string) ($col ?? '')).'"',
                $fila
            ))."\n";
        }

        return "\xEF\xBB\xBF".$output;
    }
}
