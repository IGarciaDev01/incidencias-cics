<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComentarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string', 'min:5', 'max:2000'],
            'es_interno' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'comentario.required' => 'El comentario es obligatorio.',
            'comentario.min' => 'El comentario debe tener al menos 5 caracteres.',
        ];
    }
}
