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
            'areas' => Area::where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'slug']),
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

        $query = User::where('rol', $rolEnum)->where('activo', true);

        if ($rolEnum === RolUsuario::JefeInmediato) {
            if (! $areaId) {
                return back()->withErrors(['area_id' => 'Selecciona tu área.'])->onlyInput('rol');
            }
            $query->where('area_id', $areaId);
        }

        $user = $query->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña es incorrecta.'])->onlyInput('rol');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->to($this->destino($user->rol));
    }

    public function logout(Request $request): RedirectResponse
    {
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
            RolUsuario::Subdirector => route('panel.subdireccion.incidencias.index'),
        };
    }
}
