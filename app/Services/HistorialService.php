<?php

namespace App\Services;

use App\Enums\TipoAccionHistorial;
use App\Models\HistorialIncidencia;
use App\Models\Incidencia;

class HistorialService
{
    public function registrar(
        Incidencia $incidencia,
        TipoAccionHistorial $tipo,
        ?int $userId = null,
        ?string $comentario = null,
        bool $esInterno = false,
        ?array $metadata = null,
    ): HistorialIncidencia {
        return HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userId,
            'tipo_accion' => $tipo,
            'comentario' => $comentario,
            'es_interno' => $esInterno,
            'metadata' => $metadata,
        ]);
    }
}
