<?php

namespace App\Http\Requests\Coordinador;

use Illuminate\Foundation\Http\FormRequest;

class ResolverIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector() || $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        return [
            'resolucion' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resolucion.required' => 'Debes describir la resolución de la incidencia.',
            'resolucion.min'      => 'La resolución debe tener al menos 20 caracteres.',
        ];
    }
}
