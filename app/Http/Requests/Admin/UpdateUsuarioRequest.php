<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
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
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'rol' => ['required', new Enum(RolUsuario::class)],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado por otro usuario.',
            'rol.required' => 'Debes asignar un rol.',
        ];
    }
}
