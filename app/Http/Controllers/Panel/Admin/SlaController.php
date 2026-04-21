<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Enums\Prioridad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSlaRequest;
use App\Models\SlaConfiguracion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SlaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Panel/Admin/Sla/Index', [
            'configuraciones' => SlaConfiguracion::orderByRaw(
                "FIELD(prioridad, 'alta', 'media', 'baja')"
            )->get(),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function update(UpdateSlaRequest $request): RedirectResponse
    {
        foreach ($request->sla as $config) {
            SlaConfiguracion::updateOrCreate(
                ['prioridad' => $config['prioridad']],
                [
                    'horas_primera_respuesta' => $config['horas_primera_respuesta'],
                    'horas_resolucion'        => $config['horas_resolucion'],
                    'activa'                  => $config['activa'] ?? true,
                ]
            );
        }

        return back()->with('success', 'Configuración SLA actualizada correctamente.');
    }
}
