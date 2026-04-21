<?php

namespace App\Http\Requests\Admin;

use App\Enums\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSlaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin();
    }

    public function rules(): array
    {
        return [
            'sla'                               => ['required', 'array'],
            'sla.*.prioridad'                   => ['required', new Enum(Prioridad::class)],
            'sla.*.horas_primera_respuesta'     => ['required', 'integer', 'min:1', 'max:8760'],
            'sla.*.horas_resolucion'            => ['required', 'integer', 'min:1', 'max:8760'],
            'sla.*.activa'                      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sla.required'                           => 'La configuración SLA es obligatoria.',
            'sla.*.horas_primera_respuesta.required' => 'Las horas de primera respuesta son obligatorias.',
            'sla.*.horas_resolucion.required'        => 'Las horas de resolución son obligatorias.',
            'sla.*.horas_primera_respuesta.max'      => 'El máximo es 8760 horas (1 año).',
            'sla.*.horas_resolucion.max'             => 'El máximo es 8760 horas (1 año).',
        ];
    }
}
