<?php

namespace App\Http\Requests\JefeInmediato;

use App\Enums\RolUsuario;
use App\Models\Incidencia;
use Illuminate\Foundation\Http\FormRequest;

class RechazarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if (! $user->tieneRol(RolUsuario::JefeInmediato)) {
            return false;
        }

        if (! $user->area_id) {
            return false;
        }

        $incidencia = $this->route('incidencia');
        if (! $incidencia instanceof Incidencia) {
            return false;
        }

        return (int) $incidencia->area_id === (int) $user->area_id;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de rechazo es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
