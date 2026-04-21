<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with('area:id,nombre')
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', (bool) $request->activo))
            ->when($request->filled('buscar'), fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('nombre', 'like', "%{$request->buscar}%")
                       ->orWhere('email', 'like', "%{$request->buscar}%")
                )
            )
            ->orderBy('nombre');

        return Inertia::render('Panel/Admin/Usuarios/Index', [
            'usuarios' => $query->paginate(20)->withQueryString(),
            'filtros'  => $request->only(['rol', 'activo', 'buscar']),
            'roles'    => RolUsuario::cases(),
            'areas'    => Area::where('activa', true)->get(['id', 'nombre']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Admin/Usuarios/Create', [
            'roles' => RolUsuario::cases(),
            'areas' => Area::where('activa', true)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('panel.admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        return Inertia::render('Panel/Admin/Usuarios/Edit', [
            'usuario' => $usuario->load('area:id,nombre'),
            'roles'   => RolUsuario::cases(),
            'areas'   => Area::where('activa', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('panel.admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        abort_if($usuario->id === auth()->id(), 422, 'No puedes eliminar tu propia cuenta.');

        $usuario->delete();

        return redirect()->route('panel.admin.usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }

    public function toggleActivo(User $usuario): RedirectResponse
    {
        abort_if($usuario->id === auth()->id(), 422, 'No puedes desactivar tu propia cuenta.');

        $usuario->update(['activo' => !$usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$estado} correctamente.");
    }
}
