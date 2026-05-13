<?php

namespace App\Http\Requests;

use App\Enums\TipoSolicitante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreEmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->esCapitalHumano() || $user->esSindicato() || $user->esSubdirector());
    }

    public function rules(): array
    {
        $empleadoId = $this->route('numeroEmpleado');

        return [
            'numero_empleado' => [
                'required', 'string', 'max:20',
                Rule::unique('empleados', 'numero_empleado')->ignore($empleadoId, 'numero_empleado'),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('empleados', 'email')->ignore($empleadoId, 'numero_empleado'),
            ],
            'tipo' => ['required', new Enum(TipoSolicitante::class)],
            'password' => [
                $empleadoId ? 'nullable' : 'required',
                'string', 'min:8', 'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'numero_empleado.unique' => 'Este número de empleado ya está registrado.',
            'nombre.required' => 'El nombre del empleado es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'tipo.required' => 'Debes indicar el tipo de empleado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
