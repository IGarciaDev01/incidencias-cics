<?php

namespace App\Http\Requests;

use App\Services\ArchivoService;
use Illuminate\Foundation\Http\FormRequest;

class AdjuntarArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = ArchivoService::TAMANIO_MAX_MB * 1024;

        return [
            'archivo' => [
                'required',
                'file',
                "max:{$maxKb}",
                'mimetypes:' . implode(',', ArchivoService::MIME_PERMITIDOS),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required'  => 'Debes seleccionar un archivo.',
            'archivo.max'       => 'El archivo no puede superar ' . ArchivoService::TAMANIO_MAX_MB . ' MB.',
            'archivo.mimetypes' => 'El tipo de archivo no está permitido.',
        ];
    }
}
