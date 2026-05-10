<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria')->id ?? $this->route('categoria');

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('categorias', 'slug')->ignore($categoriaId), 'regex:/^[a-z0-9-]+$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activa' => ['boolean'],
        ];
    }
}
