<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAreaRequest;
use App\Http\Requests\Admin\UpdateAreaRequest;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AreaController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Panel/Admin/Areas/Index', [
            'areas' => Area::with('jefe:id,nombre')
                ->withCount('incidencias', 'usuarios')
                ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', "%{$request->buscar}%"))
                ->orderBy('nombre')
                ->paginate(20)
                ->withQueryString(),
            'filtros' => $request->only(['buscar']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Admin/Areas/Create', [
            'jefes' => User::where('rol', RolUsuario::JefeInmediato)
                ->where('activo', true)
                ->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $jefeId = $request->filled('jefe_id') ? $request->jefe_id : null;

        $area = Area::create([...$request->validated(), 'jefe_id' => $jefeId]);

        if ($jefeId) {
            $area->usuarios()->attach($jefeId, ['es_jefe' => true]);
        }

        return redirect()->route($this->areasRoute($request, 'index'))
            ->with('success', 'Área creada correctamente.');
    }

    public function edit(Area $area): Response
    {
        return Inertia::render('Panel/Admin/Areas/Edit', [
            'area' => $area->load('jefe:id,nombre'),
            'jefes' => User::where('rol', RolUsuario::JefeInmediato)
                ->where('activo', true)
                ->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $oldJefeId = $area->jefe_id;
        $newJefeId = $request->filled('jefe_id') ? $request->jefe_id : null;

        $area->update($request->validated());

        if ($newJefeId !== $oldJefeId) {
            if ($oldJefeId) {
                $area->usuarios()->detach($oldJefeId);
            }

            if ($newJefeId) {
                $area->usuarios()->attach($newJefeId, ['es_jefe' => true]);
            }
        }

        return redirect()->route($this->areasRoute($request, 'index'))
            ->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        abort_if(
            $area->incidencias()->exists() || $area->usuarios()->exists(),
            422,
            'No se puede eliminar un área con incidencias o usuarios asociados.'
        );

        $area->delete();

        return redirect()->route($this->areasRoute(request(), 'index'))
            ->with('success', 'Área eliminada.');
    }

    private function areasRoute(Request $request, string $suffix): string
    {
        $rol = $request->user()?->rol?->value;

        if ($rol === 'capital_humano') {
            return 'panel.capital_humano.areas.'.$suffix;
        }

        return 'panel.subdireccion.admin.areas.'.$suffix;
    }
}
