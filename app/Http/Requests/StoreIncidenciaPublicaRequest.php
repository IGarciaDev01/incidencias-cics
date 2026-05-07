<?php

namespace App\Http\Requests;

use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Empleado;
use App\Services\ArchivoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreIncidenciaPublicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = ArchivoService::TAMANIO_MAX_MB * 1024;

        return [
            'numero_empleado' => ['required', 'string', 'max:20'],
            'reportante_nombre' => ['required', 'string', 'max:150'],
            'email_reportante' => ['required', 'email', 'max:150', function ($attribute, $value, $fail) {
                $email = strtolower(trim((string) $value));
                $numeroEmpleado = (string) $this->input('numero_empleado');

                $empleadoExistente = Empleado::where('numero_empleado', $numeroEmpleado)->first();

                if ($empleadoExistente && strtolower($empleadoExistente->email ?? '') === $email) {
                    return;
                }

                $existeConOtroEmpleado = Empleado::whereRaw('LOWER(email) = ?', [$email])
                    ->where('numero_empleado', '!=', $numeroEmpleado)
                    ->exists();

                if ($existeConOtroEmpleado) {
                    $fail('Este correo electrónico ya está registrado en el sistema con otro número de empleado.');
                }
            }],
            'tipo_empleado' => [
                Rule::requiredIf(function () {
                    $numero = (string) $this->input('numero_empleado');

                    $empleado = Empleado::where('numero_empleado', $numero)->first();

                    return ! $empleado || ! $empleado->tipo;
                }),
                new Enum(TipoSolicitante::class),
            ],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'fecha_incidencia' => ['required', 'date', 'before_or_equal:today'],
            'hora_incidencia' => ['nullable', 'date_format:H:i'],
            'tipo_incidencia' => ['required', new Enum(TipoIncidencia::class)],
            'minutos_retardo' => [
                Rule::when(
                    $this->tipo_incidencia === TipoIncidencia::Retardo->value,
                    ['required', 'integer', 'min:11', 'max:30'],
                    ['nullable'],
                ),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'archivos' => ['nullable', 'array', 'max:5'],
            'archivos.*' => ['file', "max:{$maxKb}", 'mimetypes:'.implode(',', ArchivoService::MIME_PERMITIDOS)],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'reportante_nombre.required' => 'El nombre del empleado es obligatorio.',
            'email_reportante.required' => 'El correo electrónico es obligatorio para recibir notificaciones.',
            'email_reportante.email' => 'Ingresa un correo electrónico válido.',
            'tipo_empleado.required' => 'Debes indicar el tipo de empleado.',
            'area_id.required' => 'El área de adscripción es obligatoria.',
            'area_id.exists' => 'El área seleccionada no es válida.',
            'fecha_incidencia.required' => 'La fecha de la incidencia es obligatoria.',
            'fecha_incidencia.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'tipo_incidencia.required' => 'El tipo de incidencia es obligatorio.',
            'minutos_retardo.required' => 'Debes indicar los minutos de retardo.',
            'minutos_retardo.min' => 'El retardo debe ser al menos 11 minutos.',
            'minutos_retardo.max' => 'El retardo debe ser menor a 31 minutos.',
            'archivos.max' => 'Puedes adjuntar un máximo de 5 archivos.',
            'archivos.*.max' => 'Cada archivo no puede superar '.ArchivoService::TAMANIO_MAX_MB.' MB.',
            'archivos.*.mimetypes' => 'El tipo de archivo no está permitido.',
        ];
    }
}
