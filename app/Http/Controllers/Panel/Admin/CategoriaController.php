<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\Prioridad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoriaRequest;
use App\Http\Requests\Admin\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Panel/Admin/Categorias/Index', [
            'categorias' => Categoria::withCount('incidencias')
                ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', "%{$request->buscar}%"))
                ->orderBy('nombre')
                ->paginate(20)
                ->withQueryString(),
            'filtros'    => $request->only(['buscar']),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Admin/Categorias/Create', [
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return redirect()->route('panel.admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): Response
    {
        return Inertia::render('Panel/Admin/Categorias/Edit', [
            'categoria'   => $categoria,
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('panel.admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        abort_if(
            $categoria->incidencias()->exists(),
            422,
            'No se puede eliminar una categoría que tiene incidencias asociadas.'
        );

        $categoria->delete();

        return redirect()->route('panel.admin.categorias.index')
            ->with('success', 'Categoría eliminada.');
    }
}
