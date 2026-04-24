<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuscarSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folio' => ['required', 'string', 'max:20'],
            'numero_empleado' => ['required_without:email', 'nullable', 'string', 'max:20'],
            'email' => ['required_without:numero_empleado', 'nullable', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El número de folio es obligatorio.',
            'numero_empleado.required_without' => 'Debes ingresar tu número de empleado o tu correo electrónico.',
            'email.required_without' => 'Debes ingresar tu correo electrónico o tu número de empleado.',
        ];
    }
}
