<?php

namespace App\Http\Controllers;

use App\Models\ArchivoAdjunto;
use App\Models\Incidencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ComprobanteController extends Controller
{
    public function descargar(Request $request, string $folio): Response
    {
        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        if (! $this->puedeVerificar($request, $incidencia)) {
            abort(403, 'No tienes acceso a este comprobante.');
        }

        $pdf = Pdf::loadView('pdf.comprobante', [
            'incidencia' => $incidencia->load(['area:id,nombre']),
        ]);

        $nombreArchivo = "comprobante-{$incidencia->folio}.pdf";

        return $pdf->download($nombreArchivo);
    }

    public function verArchivo(Request $request, string $folio, int $archivoId): BinaryFileResponse
    {
        $incidencia = Incidencia::where('folio', $folio)->firstOrFail();

        if (! $this->puedeVerificar($request, $incidencia)) {
            abort(403, 'No tienes acceso a este archivo.');
        }

        $archivo = ArchivoAdjunto::where('id', $archivoId)
            ->where('incidencia_id', $incidencia->id)
            ->firstOrFail();

        $ruta = storage_path("app/public/{$archivo->ruta_storage}");

        abort_unless(file_exists($ruta), 404, 'Archivo no encontrado.');

        return response()->file($ruta);
    }

    private function puedeVerificar(Request $request, Incidencia $incidencia): bool
    {
        $user = $request->user();

        if ($user) {
            if ($user->esSubdirector() || $user->esCapitalHumano() || $user->esSindicato()) {
                return true;
            }

            if ($user->esJefeInmediato()) {
                $areaId = $user->area_id;

                return $areaId && (int) $incidencia->area_id === $areaId;
            }
        }

        if ($request->session()->has('seguimiento_verificado') && $request->session()->get('seguimiento_verificado') === $incidencia->folio) {
            return true;
        }

        return false;
    }
}
