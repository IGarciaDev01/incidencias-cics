<?php

use App\Enums\EstadoIncidencia;
use App\Mail\IncidenciaAsignadaMail;
use App\Mail\IncidenciaCambioEstadoMail;
use App\Mail\IncidenciaConfirmadaMail;
use App\Mail\IncidenciaSolicitudInfoMail;
use App\Mail\RechazoPorLimiteMail;
use App\Mail\ResolucionFinalMail;
use App\Models\Incidencia;
use App\Models\User;

test('incidence mailables render with institutional branding', function (string $mailableClass) {
    $incidencia = Incidencia::factory()->create([
        'folio' => 'INC-2026-0001',
        'estado' => EstadoIncidencia::PendienteCapitalHumano,
        'motivo_rechazo' => null,
    ]);

    $coordinador = User::factory()->create(['nombre' => 'Coordinador de Area']);

    $mailable = match ($mailableClass) {
        IncidenciaAsignadaMail::class => new IncidenciaAsignadaMail($incidencia, $coordinador),
        IncidenciaSolicitudInfoMail::class => new IncidenciaSolicitudInfoMail($incidencia, 'Favor de adjuntar el comprobante correspondiente.'),
        RechazoPorLimiteMail::class => new RechazoPorLimiteMail($incidencia, 'Se excedio el limite permitido para el periodo.'),
        default => new $mailableClass($incidencia),
    };

    $mailable->assertSeeInHtml('Instituto Politecnico Nacional');
    $mailable->assertSeeInHtml('CICS UST');
    $mailable->assertSeeInHtml('Sistema de Gestion de Incidencias');
    $mailable->assertSeeInHtml('INC-2026-0001');
    $mailable->assertSeeInText('Mensaje automatico del Sistema de Gestion de Incidencias');
})->with([
    IncidenciaConfirmadaMail::class,
    IncidenciaCambioEstadoMail::class,
    IncidenciaAsignadaMail::class,
    IncidenciaSolicitudInfoMail::class,
    ResolucionFinalMail::class,
    RechazoPorLimiteMail::class,
]);
