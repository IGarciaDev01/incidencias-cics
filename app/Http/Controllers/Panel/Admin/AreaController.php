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
            'areas' => Area::with('subdirector:id,nombre')
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
            'subdirectores' => User::where('rol', RolUsuario::Subdirector)
                ->where('activo', true)
                ->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        Area::create($request->validated());

        return redirect()->route('panel.admin.areas.index')
            ->with('success', 'Área creada correctamente.');
    }

    public function edit(Area $area): Response
    {
        return Inertia::render('Panel/Admin/Areas/Edit', [
            'area' => $area->load('subdirector:id,nombre'),
            'subdirectores' => User::where('rol', RolUsuario::Subdirector)
                ->where('activo', true)
                ->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $area->update($request->validated());

        return redirect()->route('panel.admin.areas.index')
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

        return redirect()->route('panel.admin.areas.index')
            ->with('success', 'Área eliminada.');
    }
}
