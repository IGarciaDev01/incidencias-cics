<?php

namespace App\Http\Requests\Subdireccion;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;

class RechazarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(RolUsuario::SubdireccionAcademica, RolUsuario::Admin);
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:20', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debes indicar el motivo del rechazo.',
            'motivo.min'      => 'El motivo debe tener al menos 20 caracteres.',
        ];
    }
}
