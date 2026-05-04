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
        $query = User::with('areas:id,nombre')
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', (bool) $request->activo))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q2) => $q2->where('nombre', 'like', "%{$request->buscar}%")
                ->orWhere('email', 'like', "%{$request->buscar}%")
            )
            )
            ->orderBy('nombre');

        $usuarios = $query->paginate(20)->withQueryString();

        $usuarios->getCollection()->transform(fn ($user) => [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'rol' => $user->rol,
            'activo' => $user->activo,
            'area' => $user->area ? ['id' => $user->area->id, 'nombre' => $user->area->nombre] : null,
        ]);

        return Inertia::render('Panel/Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'filtros' => $request->only(['rol', 'activo', 'buscar']),
            'roles' => RolUsuario::cases(),
            'areas' => Area::where('activa', true)->get(['id', 'nombre']),
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
        $data = $request->validated();
        $areaId = $data['area_id'] ?? null;
        unset($data['area_id']);

        $user = User::create($data);

        if ($areaId && $user->esJefeInmediato()) {
            $user->areas()->attach($areaId, ['es_jefe' => true]);
        }

        return redirect()->route('panel.admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        return Inertia::render('Panel/Admin/Usuarios/Edit', [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo,
                'area' => $usuario->area ? ['id' => $usuario->area->id, 'nombre' => $usuario->area->nombre] : null,
            ],
            'roles' => RolUsuario::cases(),
            'areas' => Area::where('activa', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $areaId = $data['area_id'] ?? null;
        unset($data['area_id']);

        $usuario->update($data);

        if ($usuario->esJefeInmediato()) {
            if ($areaId) {
                $usuario->areas()->sync([$areaId => ['es_jefe' => true]]);
            } else {
                $usuario->areas()->detach();
            }
        }

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

        $usuario->update(['activo' => ! $usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$estado} correctamente.");
    }
}
