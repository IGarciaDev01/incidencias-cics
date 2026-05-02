<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esSubdirector();
    }

    public function rules(): array
    {
        $areaId = $this->route('area')->id ?? $this->route('area');

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('areas', 'slug')->ignore($areaId), 'regex:/^[a-z0-9-]+$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'jefe_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('rol', RolUsuario::JefeInmediato->value)->where('activo', true),
            ],
            'activa' => ['boolean'],
        ];
    }
}
