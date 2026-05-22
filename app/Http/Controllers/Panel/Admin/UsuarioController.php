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
            'numero_empleado' => $user->numero_empleado,
            'rol' => $user->rol,
            'activo' => $user->activo,
            'areas' => $user->areas->map(fn ($area) => ['id' => $area->id, 'nombre' => $area->nombre]),
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
        $roles = collect(RolUsuario::cases())->filter(function ($rol) {
            if ($rol === RolUsuario::Subdirector) {
                return ! User::where('rol', RolUsuario::Subdirector)->exists();
            }

            if ($rol === RolUsuario::CapitalHumano) {
                return ! User::where('rol', RolUsuario::CapitalHumano)->exists();
            }

            if ($rol === RolUsuario::Sindicato) {
                return ! User::where('rol', RolUsuario::Sindicato)->exists();
            }

            return true;
        })->values();

        return Inertia::render('Panel/Admin/Usuarios/Create', [
            'roles' => $roles,
            'areas' => Area::query()
                ->where('activa', true)
                ->whereRaw('not exists (select 1 from area_user au where au.area_id = areas.id and au.es_jefe = 1)')
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $areaIds = $data['area_ids'] ?? [];
        unset($data['area_ids'], $data['es_jefe']);

        $user = User::create($data);

        if ($user->esJefeInmediato() && ! empty($areaIds)) {
            $user->areas()->attach(array_fill_keys($areaIds, ['es_jefe' => true]));
            Area::whereIn('id', $areaIds)->update(['jefe_id' => $user->id]);
        }

        return redirect()->route('panel.subdireccion.admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        return Inertia::render('Panel/Admin/Usuarios/Edit', [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'numero_empleado' => $usuario->numero_empleado,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo,
                'areas' => $usuario->areas->map(fn ($area) => ['id' => $area->id, 'nombre' => $area->nombre]),
            ],
            'roles' => RolUsuario::cases(),
            'areas' => Area::query()
                ->where('activa', true)
                ->where(function ($q) use ($usuario) {
                    $jefeAreaIds = $usuario->areas->pluck('id');
                    $q->whereRaw('not exists (select 1 from area_user au where au.area_id = areas.id and au.es_jefe = 1)')
                        ->orWhereIn('id', $jefeAreaIds);
                })
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $data = $request->validated();
        $oldJefeAreaIds = $usuario->areas()->wherePivot('es_jefe', true)->pluck('areas.id')->toArray();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $areaIds = $data['area_ids'] ?? [];
        unset($data['area_ids'], $data['es_jefe']);

        $usuario->update($data);

        if ($usuario->esJefeInmediato()) {
            $currentJefeAreaIds = $oldJefeAreaIds;
            $newAreaIds = $areaIds;

            $toRemove = array_diff($currentJefeAreaIds, $newAreaIds);
            if (! empty($toRemove)) {
                Area::whereIn('id', $toRemove)->update(['jefe_id' => null]);
            }
            $usuario->areas()->detach();
            $usuario->areas()->attach(array_fill_keys($areaIds, ['es_jefe' => true]));
            Area::whereIn('id', $areaIds)->update(['jefe_id' => $usuario->id]);
        } elseif (! empty($oldJefeAreaIds)) {
            Area::whereIn('id', $oldJefeAreaIds)->update(['jefe_id' => null]);
            $usuario->areas()->detach();
        }

        return redirect()->route('panel.subdireccion.admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        abort_if($usuario->id === auth()->id(), 422, 'No puedes eliminar tu propia cuenta.');

        $usuario->delete();

        return redirect()->route('panel.subdireccion.admin.usuarios.index')
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
