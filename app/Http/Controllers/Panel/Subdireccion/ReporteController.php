<?php

namespace App\Http\Controllers\Panel\Subdireccion;

use App\Enums\AuditAction;
use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Incidencia;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): Response
    {
        $filtros = $this->resolveFiltros($request);
        $base = $this->queryFiltrada($filtros);

        $user = $request->user();
        $view = $user && $user->esCapitalHumano()
            ? 'Panel/CapitalHumano/Reportes/Index'
            : 'Panel/Subdireccion/Reportes/Index';

        $porEstado = (clone $base)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $total = $porEstado->sum();

        return Inertia::render($view, [
            'filtros' => $this->filtrosParaVista($filtros),

            'estadisticas' => [
                'total' => $total,
                'aprobadas' => (int) ($porEstado[EstadoIncidencia::Aprobada->value] ?? 0),
                'rechazadas' => (int) ($porEstado[EstadoIncidencia::Rechazada->value] ?? 0),
                'pendientes' => $total - (int) ($porEstado[EstadoIncidencia::Aprobada->value] ?? 0) - (int) ($porEstado[EstadoIncidencia::Rechazada->value] ?? 0),
            ],

            'porEstado' => $porEstado,

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

            'porDia' => (clone $base)
                ->selectRaw('DATE(fecha_incidencia) as dia, count(*) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->pluck('total', 'dia'),

            'opciones' => [
                'estados' => collect(EstadoIncidencia::cases())->map(fn (EstadoIncidencia $estado) => [
                    'value' => $estado->value,
                    'label' => $estado->label(),
                ]),
                'tiposIncidencia' => collect(TipoIncidencia::cases())->map(fn (TipoIncidencia $tipo) => [
                    'value' => $tipo->value,
                    'label' => $tipo->label(),
                ]),
                'tiposSolicitante' => collect(TipoSolicitante::cases())->map(fn (TipoSolicitante $tipo) => [
                    'value' => $tipo->value,
                    'label' => $tipo->label(),
                ]),
                'areas' => Area::query()->orderBy('nombre')->get(['id', 'nombre']),
            ],
        ]);
    }

    public function exportar(Request $request): HttpResponse
    {
        $filtros = $this->resolveFiltros($request);

        $incidencias = $this->queryFiltrada($filtros)
            ->with('area:id,nombre')
            ->orderByDesc('created_at')
            ->get();

        $csv = $this->generarCsv($incidencias);

        $this->auditLogService->record(
            action: AuditAction::ReporteExportado,
            description: 'Reporte de incidencias exportado.',
            metadata: [
                ...$this->filtrosParaVista($filtros),
                'total_incidencias' => $incidencias->count(),
            ],
        );

        $desde = $filtros['desde'];
        $hasta = $filtros['hasta'];
        $suffix = $desde->isSameDay($hasta)
            ? $desde->toDateString()
            : $desde->toDateString().'-'.$hasta->toDateString();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"reporte-incidencias-{$suffix}.csv\"",
        ]);
    }

    /**
     * @return array{desde: Carbon, hasta: Carbon, fecha: ?string, estado: ?string, tipo_incidencia: ?string, tipo_solicitante: ?string, area_id: ?int}
     */
    private function resolveFiltros(Request $request): array
    {
        $validated = $request->validate([
            'fecha' => ['nullable', 'date'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'estado' => ['nullable', Rule::enum(EstadoIncidencia::class)],
            'tipo_incidencia' => ['nullable', Rule::enum(TipoIncidencia::class)],
            'tipo_solicitante' => ['nullable', Rule::enum(TipoSolicitante::class)],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ]);

        if ($request->filled('fecha')) {
            $desde = Carbon::parse($validated['fecha'])->startOfDay();
            $hasta = Carbon::parse($validated['fecha'])->endOfDay();
            $fecha = $desde->toDateString();
        } else {
            $desde = $request->filled('desde') ? Carbon::parse($validated['desde'])->startOfDay() : now()->startOfMonth();
            $hasta = $request->filled('hasta') ? Carbon::parse($validated['hasta'])->endOfDay() : now()->endOfMonth();
            $fecha = null;
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'fecha' => $fecha,
            'estado' => $validated['estado'] ?? null,
            'tipo_incidencia' => $validated['tipo_incidencia'] ?? null,
            'tipo_solicitante' => $validated['tipo_solicitante'] ?? null,
            'area_id' => isset($validated['area_id']) ? (int) $validated['area_id'] : null,
        ];
    }

    /**
     * @param  array{desde: Carbon, hasta: Carbon, fecha: ?string, estado: ?string, tipo_incidencia: ?string, tipo_solicitante: ?string, area_id: ?int}  $filtros
     */
    private function queryFiltrada(array $filtros): Builder
    {
        return Incidencia::query()
            ->whereBetween('fecha_incidencia', [$filtros['desde']->toDateString(), $filtros['hasta']->toDateString()])
            ->when($filtros['estado'], fn (Builder $query, string $estado) => $query->where('estado', $estado))
            ->when($filtros['tipo_incidencia'], fn (Builder $query, string $tipoIncidencia) => $query->where('tipo_incidencia', $tipoIncidencia))
            ->when($filtros['tipo_solicitante'], fn (Builder $query, string $tipoSolicitante) => $query->where('tipo_solicitante', $tipoSolicitante))
            ->when($filtros['area_id'], fn (Builder $query, int $areaId) => $query->where('area_id', $areaId));
    }

    /**
     * @param  array{desde: Carbon, hasta: Carbon, fecha: ?string, estado: ?string, tipo_incidencia: ?string, tipo_solicitante: ?string, area_id: ?int}  $filtros
     * @return array{desde: string, hasta: string, fecha: string, estado: string, tipo_incidencia: string, tipo_solicitante: string, area_id: string}
     */
    private function filtrosParaVista(array $filtros): array
    {
        return [
            'desde' => $filtros['desde']->toDateString(),
            'hasta' => $filtros['hasta']->toDateString(),
            'fecha' => $filtros['fecha'] ?? '',
            'estado' => $filtros['estado'] ?? '',
            'tipo_incidencia' => $filtros['tipo_incidencia'] ?? '',
            'tipo_solicitante' => $filtros['tipo_solicitante'] ?? '',
            'area_id' => $filtros['area_id'] ? (string) $filtros['area_id'] : '',
        ];
    }

    /**
     * @param  Collection<int, Incidencia>  $incidencias
     */
    private function generarCsv(Collection $incidencias): string
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
            'Motivo Rechazo',
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
            $i->motivo_rechazo ?? '',
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
