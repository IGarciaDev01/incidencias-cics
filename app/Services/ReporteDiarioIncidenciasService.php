<?php

namespace App\Services;

use App\Models\ArchivoAdjunto;
use App\Models\Incidencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ReporteDiarioIncidenciasService
{
    private const TIMEZONE = 'America/Mexico_City';

    /**
     * @return array{fecha: string, total: int, zip_path: string, zip_name: string}
     */
    public function generar(?string $fecha = null): array
    {
        $dia = $fecha
            ? CarbonImmutable::parse($fecha, self::TIMEZONE)
            : CarbonImmutable::now(self::TIMEZONE);

        $fechaReporte = $dia->toDateString();
        $directorio = "reportes/incidencias-diarias/{$fechaReporte}";
        $zipName = "incidencias-{$fechaReporte}.zip";
        $zipRelativePath = "{$directorio}/{$zipName}";

        Storage::disk('local')->deleteDirectory($directorio);
        Storage::disk('local')->makeDirectory($directorio);

        $zipPath = Storage::disk('local')->path($zipRelativePath);
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("No se pudo crear el archivo ZIP del reporte diario: {$zipPath}");
        }

        $incidencias = $this->incidenciasDelDia($dia);

        $zip->addFromString("resumen-incidencias-{$fechaReporte}.csv", $this->generarCsv($incidencias));

        foreach ($incidencias as $incidencia) {
            $carpeta = $this->nombreCarpetaIncidencia($incidencia);

            $pdf = Pdf::loadView('pdf.comprobante', [
                'incidencia' => $incidencia,
            ])->output();

            $zip->addFromString("{$carpeta}/comprobante-{$incidencia->folio}.pdf", $pdf);

            foreach ($incidencia->archivos as $archivo) {
                $this->agregarAdjunto($zip, $carpeta, $archivo);
            }
        }

        $zip->close();

        return [
            'fecha' => $fechaReporte,
            'total' => $incidencias->count(),
            'zip_path' => $zipPath,
            'zip_name' => $zipName,
        ];
    }

    /**
     * @return Collection<int, Incidencia>
     */
    private function incidenciasDelDia(CarbonImmutable $dia): Collection
    {
        return Incidencia::query()
            ->with(['area:id,nombre', 'archivos'])
            ->whereBetween('created_at', [
                $dia->startOfDay()->toDateTimeString(),
                $dia->endOfDay()->toDateTimeString(),
            ])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param  Collection<int, Incidencia>  $incidencias
     */
    private function generarCsv(Collection $incidencias): string
    {
        $cabeceras = [
            'Folio',
            'No. Empleado',
            'Nombre',
            'Correo',
            'Tipo Solicitante',
            'Area',
            'Fecha Incidencia',
            'Tipo Incidencia',
            'Minutos Retardo',
            'Estado',
            'Motivo Rechazo',
            'Registrada',
            'Adjuntos',
        ];

        $output = implode(',', array_map(fn (string $cabecera) => '"'.$cabecera.'"', $cabeceras))."\n";

        foreach ($incidencias as $incidencia) {
            $fila = [
                $incidencia->folio,
                $incidencia->numero_empleado,
                $incidencia->reportante_nombre,
                $incidencia->email_reportante ?? '',
                $incidencia->tipo_solicitante->label(),
                $incidencia->area?->nombre ?? 'Sin area',
                $incidencia->fecha_incidencia->format('d/m/Y'),
                $incidencia->tipo_incidencia->label(),
                $incidencia->minutos_retardo ?? '',
                $incidencia->estado->label(),
                $incidencia->motivo_rechazo ?? '',
                $incidencia->created_at->format('d/m/Y H:i'),
                $incidencia->archivos->count(),
            ];

            $output .= implode(',', array_map(
                fn (mixed $columna) => '"'.str_replace('"', '""', (string) ($columna ?? '')).'"',
                $fila
            ))."\n";
        }

        return "\xEF\xBB\xBF".$output;
    }

    private function agregarAdjunto(ZipArchive $zip, string $carpeta, ArchivoAdjunto $archivo): void
    {
        if (! Storage::disk('public')->exists($archivo->ruta_storage)) {
            return;
        }

        $zip->addFile(
            Storage::disk('public')->path($archivo->ruta_storage),
            "{$carpeta}/adjuntos/{$archivo->id}-{$this->nombreArchivoSeguro($archivo->nombre_original)}"
        );
    }

    private function nombreCarpetaIncidencia(Incidencia $incidencia): string
    {
        return $incidencia->folio.'-'.Str::slug($incidencia->reportante_nombre ?: 'sin-nombre');
    }

    private function nombreArchivoSeguro(string $nombre): string
    {
        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $seguro = Str::slug(Str::ascii($base)) ?: 'archivo';

        return $extension ? "{$seguro}.{$extension}" : $seguro;
    }
}
