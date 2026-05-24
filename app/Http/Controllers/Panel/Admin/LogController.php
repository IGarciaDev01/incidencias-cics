<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string'],
            'actor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'folio' => ['nullable', 'string', 'max:20'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);

        $validActions = collect(AuditAction::cases())->map->value->all();

        if (($validated['action'] ?? null) && ! in_array($validated['action'], $validActions, true)) {
            abort(422, 'El tipo de acción seleccionado no es válido.');
        }

        $query = AuditLog::with([
            'incidencia:id,folio,reportante_nombre,estado',
            'actor:id,nombre,rol',
        ])
            ->when($request->filled('action'), fn ($q) => $q->where('action', $validated['action']))
            ->when($request->filled('actor_user_id'), fn ($q) => $q->where('actor_user_id', $validated['actor_user_id']))
            ->when($request->filled('folio'), fn ($q) => $q->whereHas('incidencia', fn ($q2) => $q2->where('folio', 'like', "%{$validated['folio']}%")
            )
            )
            ->when($request->filled('desde'), fn ($q) => $q->where('created_at', '>=', $request->date('desde')->startOfDay()))
            ->when($request->filled('hasta'), fn ($q) => $q->where('created_at', '<=', $request->date('hasta')->endOfDay()))
            ->latest('created_at');

        $logs = $query->paginate(50)->withQueryString();

        $logs->getCollection()->transform(fn (AuditLog $log) => [
            'id' => $log->id,
            'action' => $log->action->value,
            'action_label' => $log->action->label(),
            'action_category' => $log->action->category(),
            'description' => $log->description,
            'metadata' => $log->metadata,
            'actor_type' => $log->actor_type,
            'actor_identifier' => $log->actor_identifier,
            'created_at' => $log->created_at->toISOString(),
            'incidencia' => $log->incidencia ? [
                'id' => $log->incidencia->id,
                'folio' => $log->incidencia->folio,
                'reportante_nombre' => $log->incidencia->reportante_nombre,
                'estado' => $log->incidencia->estado->value,
            ] : null,
            'actor' => $log->actor ? [
                'id' => $log->actor->id,
                'nombre' => $log->actor->nombre,
                'rol' => $log->actor->rol->value,
            ] : null,
        ]);

        return Inertia::render('Panel/Admin/Logs/Index', [
            'logs' => $logs,
            'filtros' => $request->only(['action', 'actor_user_id', 'folio', 'desde', 'hasta']),
            'acciones' => collect(AuditAction::cases())->map(fn (AuditAction $action) => [
                'value' => $action->value,
                'label' => $action->label(),
                'category' => $action->category(),
            ]),
            'usuarios' => User::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }
}
