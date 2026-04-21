<?php

namespace App\Http\Requests\Coordinador;

use Illuminate\Foundation\Http\FormRequest;

class EscalarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esCoordinador() || $this->user()?->esAdmin();
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
            'motivo.required' => 'Debes indicar el motivo de la escalada.',
            'motivo.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
