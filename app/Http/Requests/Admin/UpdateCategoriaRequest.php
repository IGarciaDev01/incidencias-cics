<?php

namespace App\Http\Requests\Admin;

use App\Enums\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria')->id ?? $this->route('categoria');

        return [
            'nombre'           => ['required', 'string', 'max:150'],
            'slug'             => ['required', 'string', 'max:150', Rule::unique('categorias', 'slug')->ignore($categoriaId), 'regex:/^[a-z0-9-]+$/'],
            'descripcion'      => ['nullable', 'string', 'max:1000'],
            'prioridad_defecto' => ['required', new Enum(Prioridad::class)],
            'activa'           => ['boolean'],
        ];
    }
}
