<?php

namespace App\Console\Commands;

use App\Enums\RolUsuario;
use App\Mail\ReporteDiarioIncidenciasMail;
use App\Models\User;
use App\Services\ReporteDiarioIncidenciasService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('incidencias:enviar-reporte-diario {--fecha= : Fecha del reporte en formato YYYY-MM-DD}')]
#[Description('Envia a Capital Humano el ZIP diario con CSV, comprobantes PDF y adjuntos de incidencias.')]
class EnviarReporteDiarioIncidencias extends Command
{
    public function handle(ReporteDiarioIncidenciasService $reporteService): int
    {
        $capitalHumano = User::query()
            ->where('rol', RolUsuario::CapitalHumano)
            ->where('activo', true)
            ->first();

        if (! $capitalHumano) {
            $this->error('No se encontro un usuario activo de Capital Humano para enviar el reporte diario.');

            return self::FAILURE;
        }

        $reporte = $reporteService->generar($this->option('fecha') ?: null);

        Mail::to($capitalHumano->email)->queue(new ReporteDiarioIncidenciasMail(
            fecha: $reporte['fecha'],
            totalIncidencias: $reporte['total'],
            zipPath: $reporte['zip_path'],
            zipName: $reporte['zip_name'],
        ));

        $this->info("Reporte diario {$reporte['zip_name']} encolado para {$capitalHumano->email}.");

        return self::SUCCESS;
    }
}
