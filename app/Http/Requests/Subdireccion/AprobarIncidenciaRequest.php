<?php

namespace App\Http\Requests\Subdireccion;

use Illuminate\Foundation\Http\FormRequest;

class AprobarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector() || $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
