<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\TipoAccionHistorial;
use App\Http\Controllers\Controller;
use App\Models\HistorialIncidencia;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = HistorialIncidencia::with([
            'incidencia:id,folio,reportante_nombre,estado',
            'user:id,nombre',
        ])
            ->when($request->filled('tipo_accion'), fn ($q) => $q->where('tipo_accion', $request->tipo_accion))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('folio'), fn ($q) => $q->whereHas('incidencia', fn ($q2) => $q2->where('folio', 'like', "%{$request->folio}%")
            )
            )
            ->when($request->filled('desde'), fn ($q) => $q->where('created_at', '>=', $request->date('desde')->startOfDay()))
            ->when($request->filled('hasta'), fn ($q) => $q->where('created_at', '<=', $request->date('hasta')->endOfDay()))
            ->latest('created_at');

        return Inertia::render('Panel/Admin/Logs/Index', [
            'logs' => $query->paginate(50)->withQueryString(),
            'filtros' => $request->only(['tipo_accion', 'user_id', 'folio', 'desde', 'hasta']),
            'tiposAccion' => TipoAccionHistorial::cases(),
            'usuarios' => User::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }
}
