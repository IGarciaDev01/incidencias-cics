<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')->id ?? $this->route('usuario');

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'numero_empleado' => [Rule::requiredIf($this->rol === RolUsuario::JefeInmediato->value), 'nullable', 'string', 'max:20', Rule::unique('users', 'numero_empleado')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'rol' => [
                'required',
                new Enum(RolUsuario::class),
                function ($attribute, $value, $fail) use ($userId) {
                    $rol = RolUsuario::from($value);

                    if (! in_array($rol, [RolUsuario::Subdirector, RolUsuario::CapitalHumano, RolUsuario::Sindicato], true)) {
                        return;
                    }

                    $exists = User::where('rol', $rol)
                        ->whereKeyNot($userId)
                        ->exists();

                    if ($exists) {
                        $fail('Ya existe un usuario con este rol. Solo puede haber uno.');
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
            'email.unique' => 'Este correo ya está registrado por otro usuario.',
            'numero_empleado.unique' => 'Este número de empleado ya está registrado.',
            'rol.required' => 'Debes asignar un rol.',
        ];
    }
}
