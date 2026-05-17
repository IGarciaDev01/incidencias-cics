<?php

namespace App\Http\Requests\CapitalHumano;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;

class EnviarSindicatoIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(RolUsuario::CapitalHumano);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
