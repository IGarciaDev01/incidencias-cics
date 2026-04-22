<?php

namespace App\Http\Controllers;

use App\Enums\RolUsuario;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('auth/login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Avoid redirecting to stale "intended" URLs from previous sessions that
        // may belong to another role group (which would cause a 403).
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
            RolUsuario::Admin => route('panel.dashboard'),
            RolUsuario::JefeInmediato => route('panel.jefe_inmediato.incidencias.index'),
            RolUsuario::CapitalHumano => route('panel.capital_humano.incidencias.index'),
            RolUsuario::SubdireccionAcademica => route('panel.subdireccion.incidencias.index'),
        };
    }
}
