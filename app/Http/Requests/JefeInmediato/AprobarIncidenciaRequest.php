<?php

namespace App\Http\Requests\JefeInmediato;

use App\Enums\RolUsuario;
use App\Models\Incidencia;
use Illuminate\Foundation\Http\FormRequest;

class AprobarIncidenciaRequest extends FormRequest
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

        if (! $user->tieneArea()) {
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
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
