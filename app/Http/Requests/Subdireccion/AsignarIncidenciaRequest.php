<?php

namespace App\Http\Requests\Subdireccion;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignarIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector() || $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        return [
            'coordinador_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('rol', RolUsuario::Coordinador->value)->where('activo', true),
            ],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'coordinador_id.required' => 'Debes seleccionar un coordinador.',
            'coordinador_id.exists'   => 'El coordinador seleccionado no es válido.',
        ];
    }
}
