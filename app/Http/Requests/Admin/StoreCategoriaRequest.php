<?php

namespace App\Http\Requests\Admin;

use App\Enums\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:categorias,slug', 'regex:/^[a-z0-9-]+$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'prioridad_defecto' => ['required', new Enum(Prioridad::class)],
            'activa' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'prioridad_defecto.required' => 'Debes indicar la prioridad por defecto.',
        ];
    }
}
