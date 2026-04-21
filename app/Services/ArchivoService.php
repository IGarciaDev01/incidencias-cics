<?php

namespace App\Services;

use App\Models\ArchivoAdjunto;
use App\Models\Incidencia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ArchivoService
{
    /** Tipos MIME permitidos */
    public const MIME_PERMITIDOS = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
    ];

    public const TAMANIO_MAX_MB = 10;

    public function almacenar(UploadedFile $file, Incidencia $incidencia, ?int $subidoPorId = null): ArchivoAdjunto
    {
        $ruta = $file->store("incidencias/{$incidencia->folio}", 'public');

        return ArchivoAdjunto::create([
            'incidencia_id'  => $incidencia->id,
            'nombre_original' => $file->getClientOriginalName(),
            'ruta_storage'   => $ruta,
            'mime_type'      => $file->getMimeType(),
            'tamanio_bytes'  => $file->getSize(),
            'subido_por'     => $subidoPorId,
        ]);
    }

    public function eliminar(ArchivoAdjunto $archivo): void
    {
        Storage::disk('public')->delete($archivo->ruta_storage);
        $archivo->delete();
    }
}
