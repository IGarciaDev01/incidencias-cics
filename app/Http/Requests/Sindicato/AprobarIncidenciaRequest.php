<?php

namespace App\Http\Requests\Sindicato;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;

class AprobarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(RolUsuario::Sindicato);
    }

    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
