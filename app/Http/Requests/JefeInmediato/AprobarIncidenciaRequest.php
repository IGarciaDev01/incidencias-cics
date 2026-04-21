<?php

namespace App\Http\Requests\JefeInmediato;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;

class AprobarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(RolUsuario::JefeInmediato, RolUsuario::Admin);
    }

    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
