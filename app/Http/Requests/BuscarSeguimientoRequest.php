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
            'token' => ['required_without:email', 'nullable', 'string', 'size:64'],
            'email' => ['required_without:token', 'nullable', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required'            => 'El número de folio es obligatorio.',
            'token.required_without'    => 'Debes ingresar el token de seguimiento o tu correo electrónico.',
            'email.required_without'    => 'Debes ingresar tu correo electrónico o el token de seguimiento.',
        ];
    }
}
