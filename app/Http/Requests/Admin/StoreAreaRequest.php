<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:areas,slug', 'regex:/^[a-z0-9-]+$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'jefe_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('rol', RolUsuario::JefeInmediato->value)->where('activo', true),
            ],
            'activa' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'jefe_id.exists' => 'El jefe seleccionado no es válido.',
        ];
    }
}
