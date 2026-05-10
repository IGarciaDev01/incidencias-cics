<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_empleado' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
