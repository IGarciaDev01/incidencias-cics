<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\ArchivoAdjunto;
use App\Models\Incidencia;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ComprobanteController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

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

        $this->auditLogService->record(
            action: AuditAction::ComprobanteDescargado,
            description: "Comprobante de la incidencia {$incidencia->folio} descargado.",
            subject: $incidencia,
            metadata: ['archivo' => $nombreArchivo],
            actorType: $request->user() ? null : $this->actorTypeFromSession($request),
            actorIdentifier: $request->user() ? null : $this->actorIdentifierFromSession($request),
        );

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

        $this->auditLogService->record(
            action: AuditAction::ArchivoConsultado,
            description: "Archivo de la incidencia {$incidencia->folio} consultado.",
            subject: $archivo,
            metadata: [
                'folio' => $incidencia->folio,
                'nombre_original' => $archivo->nombre_original,
            ],
            incidencia: $incidencia,
            actorType: $request->user() ? null : $this->actorTypeFromSession($request),
            actorIdentifier: $request->user() ? null : $this->actorIdentifierFromSession($request),
        );

        return response()->file($ruta);
    }

    private function actorTypeFromSession(Request $request): ?string
    {
        return $request->session()->has('empleado_auth') ? 'empleado' : null;
    }

    private function actorIdentifierFromSession(Request $request): ?string
    {
        return $request->session()->get('empleado_auth');
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

        if ($request->session()->get('empleado_auth') === $incidencia->numero_empleado) {
            return true;
        }

        return false;
    }
}
