<?php

namespace App\Http\Requests\Coordinador;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector() || $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        return [
            'mensaje' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.min'      => 'El mensaje debe tener al menos 10 caracteres.',
        ];
    }
}
