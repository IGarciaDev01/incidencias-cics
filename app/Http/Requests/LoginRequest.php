<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rol = $this->input('rol');

        $rules = [
            'rol' => ['required', 'string', 'in:jefe_inmediato,capital_humano,subdirector'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];

        if ($rol === 'jefe_inmediato') {
            $rules['area_id'] = ['required', 'integer', 'exists:areas,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'rol.required' => 'Selecciona un rol.',
            'rol.in' => 'Rol inválido.',
            'password.required' => 'La contraseña es obligatoria.',
            'area_id.required' => 'Selecciona tu área.',
            'area_id.exists' => 'El área seleccionada no es válida.',
        ];
    }
}
