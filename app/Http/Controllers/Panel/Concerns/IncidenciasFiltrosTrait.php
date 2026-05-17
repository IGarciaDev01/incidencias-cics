<?php

namespace App\Http\Controllers\Panel\Concerns;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Area;
use App\Models\Incidencia;
use Illuminate\Http\Request;

trait IncidenciasFiltrosTrait
{
    protected function listadoIncidencias(Request $request, ?int $areaId = null, ?callable $queryCallback = null): array
    {
        $query = Incidencia::with(['area:id,nombre']);

        if ($queryCallback) {
            $queryCallback($query);
        }

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        $query
            ->when($request->filled('fecha_inicio'), fn ($q) => $q->whereDate('fecha_incidencia', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn ($q) => $q->whereDate('fecha_incidencia', '<=', $request->fecha_fin))
            ->when($request->string('estado')->isNotEmpty(), function ($q) use ($request) {
                $q->where('estado', $request->estado);
            })
            ->when($request->string('tipo')->isNotEmpty(), function ($q) use ($request) {
                $q->where('tipo_incidencia', $request->tipo);
            })
            ->when($request->filled('area_id'), fn ($q) => $q->where('area_id', $request->area_id))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $buscar = addcslashes($request->buscar, '%_');

                $q->where(fn ($q2) => $q2->where('folio', 'like', "%{$buscar}%")
                    ->orWhere('numero_empleado', 'like', "%{$buscar}%")
                    ->orWhere('reportante_nombre', 'like', "%{$buscar}%")
                );
            })
            ->latest();

        $data = [
            'incidencias' => $query->paginate(20)->withQueryString(),
            'filtros' => $request->only(['estado', 'tipo', 'buscar', 'area_id', 'fecha_inicio', 'fecha_fin']),
            'estados' => array_map(fn ($e) => ['value' => $e->value, 'name' => $e->name], EstadoIncidencia::cases()),
            'tipos' => array_map(fn ($t) => ['value' => $t->value, 'name' => $t->name], TipoIncidencia::cases()),
        ];

        if (! $areaId) {
            $data['areas'] = Area::orderBy('nombre')->get(['id', 'nombre']);
        }

        return $data;
    }

    protected function incidenciaConRelaciones(Incidencia $incidencia): Incidencia
    {
        $incidencia->load([
            'area:id,nombre',
            'revisadoPor:id,nombre',
            'historial.user:id,nombre',
            'archivos',
        ]);

        return $incidencia;
    }
}
