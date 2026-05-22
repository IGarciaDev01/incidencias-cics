<?php

namespace App\Http\Controllers;

use App\Enums\RolUsuario;
use App\Http\Requests\LoginRequest;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('auth/login', [
            'areas' => Area::conJefeOperativo()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'slug']),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $rol = $request->input('rol');
        $password = $request->input('password');
        $areaId = $request->input('area_id');

        try {
            $rolEnum = RolUsuario::from($rol);
        } catch (\ValueError) {
            return back()->withErrors(['rol' => 'Rol inválido.'])->onlyInput('rol');
        }

        if ($rolEnum === RolUsuario::JefeInmediato) {
            if (! $areaId) {
                return back()->withErrors(['area_id' => 'Selecciona tu área.'])->onlyInput('rol');
            }

            $area = Area::whereKey($areaId)
                ->conJefeOperativo()
                ->first();

            if (! $area) {
                return back()->withErrors(['area_id' => 'El área seleccionada no tiene jefe asignado.'])->onlyInput('rol');
            }

            $user = User::whereKey($area->jefe_id)
                ->whereExists(fn ($q) => $q->from('area_user')
                    ->join('areas', 'area_user.area_id', '=', 'areas.id')
                    ->whereColumn('area_user.user_id', 'users.id')
                    ->where('area_user.area_id', $areaId)
                    ->where('area_user.es_jefe', true)
                )->where('rol', $rolEnum)->where('activo', true)->first();

            if (! $user) {
                return back()->withErrors(['area_id' => 'No hay un jefe asignado a esta área.'])->onlyInput('rol');
            }
        } else {
            $user = User::where('rol', $rolEnum)->where('activo', true)->first();

            if (! $user) {
                return back()->withErrors(['rol' => 'No se encontró un usuario activo para este rol.'])->onlyInput('rol');
            }
        }

        if (! Hash::check($password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña es incorrecta.'])->onlyInput('rol');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($rolEnum === RolUsuario::JefeInmediato) {
            $request->session()->put('area_id', $areaId);
        }

        return redirect()->to($this->destino($user->rol));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('area_id');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function destino(RolUsuario $rol): string
    {
        return match ($rol) {
            RolUsuario::JefeInmediato => route('panel.jefe_inmediato.incidencias.index'),
            RolUsuario::CapitalHumano => route('panel.capital_humano.incidencias.index'),
            RolUsuario::Sindicato => route('panel.sindicato.incidencias.index'),
            RolUsuario::Subdirector => route('panel.subdireccion.incidencias.index'),
        };
    }
}
