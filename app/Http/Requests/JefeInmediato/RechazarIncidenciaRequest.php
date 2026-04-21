<?php

namespace App\Http\Requests\JefeInmediato;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;

class RechazarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(RolUsuario::JefeInmediato, RolUsuario::Admin);
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
            'motivo.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
