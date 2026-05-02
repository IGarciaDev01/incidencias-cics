<?php

namespace App\Http\Requests\Subdireccion;

use Illuminate\Foundation\Http\FormRequest;

class AprobarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
