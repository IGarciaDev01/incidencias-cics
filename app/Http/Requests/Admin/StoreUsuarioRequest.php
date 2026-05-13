<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'numero_empleado' => ['nullable', 'string', 'max:20', 'unique:users,numero_empleado'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol' => [
                'required',
                new Enum(RolUsuario::class),
                function ($attribute, $value, $fail) {
                    $rol = RolUsuario::from($value);

                    if ($rol === RolUsuario::Subdirector && User::where('rol', RolUsuario::Subdirector)->exists()) {
                        $fail('Ya existe un usuario subdirección. Solo puede haber uno.');
                    }

                    if ($rol === RolUsuario::CapitalHumano && User::where('rol', RolUsuario::CapitalHumano)->exists()) {
                        $fail('Ya existe un usuario capital humano. Solo puede haber uno.');
                    }

                    if ($rol === RolUsuario::Sindicato && User::where('rol', RolUsuario::Sindicato)->exists()) {
                        $fail('Ya existe un usuario sindicato. Solo puede haber uno.');
                    }
                },
            ],
            'area_ids' => ['nullable', 'array'],
            'area_ids.*' => ['integer', 'exists:areas,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'numero_empleado.unique' => 'Este número de empleado ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'rol.required' => 'Debes asignar un rol.',
        ];
    }
}
