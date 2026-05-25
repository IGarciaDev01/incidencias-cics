<?php

use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;
use App\Mail\ReporteDiarioIncidenciasMail;
use App\Models\ArchivoAdjunto;
use App\Models\Area;
use App\Models\Incidencia;
use App\Models\User;
use App\Services\ReporteDiarioIncidenciasService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('genera zip diario con csv pdfs y adjuntos', function () {
    Storage::fake('public');

    $area = Area::factory()->create(['nombre' => 'Capital Humano']);
    $incidencia = Incidencia::factory()->create([
        'folio' => 'INC-2026-REPORTE',
        'area_id' => $area->id,
        'reportante_nombre' => 'Empleado Reporte',
        'estado' => EstadoIncidencia::PendienteJefe,
        'created_at' => '2026-05-24 10:30:00',
    ]);

    Incidencia::factory()->create([
        'folio' => 'INC-2026-OTRO-DIA',
        'created_at' => '2026-05-23 10:30:00',
    ]);

    Storage::disk('public')->put('incidencias/INC-2026-REPORTE/evidencia.txt', 'contenido de evidencia');

    ArchivoAdjunto::create([
        'incidencia_id' => $incidencia->id,
        'nombre_original' => 'Evidencia de prueba.txt',
        'ruta_storage' => 'incidencias/INC-2026-REPORTE/evidencia.txt',
        'mime_type' => 'text/plain',
        'tamanio_bytes' => 22,
    ]);

    $reporte = app(ReporteDiarioIncidenciasService::class)->generar('2026-05-24');

    expect($reporte['total'])->toBe(1)
        ->and(file_exists($reporte['zip_path']))->toBeTrue();

    $zip = new ZipArchive;
    expect($zip->open($reporte['zip_path']))->toBeTrue();

    $nombres = collect(range(0, $zip->numFiles - 1))
        ->map(fn (int $index) => $zip->getNameIndex($index));

    expect($nombres)->toContain('resumen-incidencias-2026-05-24.csv')
        ->and($nombres->contains(fn (?string $nombre) => str_contains((string) $nombre, 'comprobante-INC-2026-REPORTE.pdf')))->toBeTrue()
        ->and($nombres->contains(fn (?string $nombre) => str_contains((string) $nombre, 'adjuntos/1-evidencia-de-prueba.txt')))->toBeTrue()
        ->and($zip->getFromName('resumen-incidencias-2026-05-24.csv'))->toContain('INC-2026-REPORTE')
        ->and($zip->getFromName('resumen-incidencias-2026-05-24.csv'))->not->toContain('INC-2026-OTRO-DIA');

    $zip->close();
});

test('comando encola reporte diario para capital humano', function () {
    Mail::fake();

    $capitalHumano = User::factory()->create([
        'email' => 'capital.humano@test.com',
        'rol' => RolUsuario::CapitalHumano,
        'activo' => true,
    ]);

    Incidencia::factory()->create([
        'folio' => 'INC-2026-COMANDO',
        'created_at' => '2026-05-24 12:00:00',
    ]);

    $this->artisan('incidencias:enviar-reporte-diario', ['--fecha' => '2026-05-24'])
        ->assertExitCode(0);

    Mail::assertQueued(ReporteDiarioIncidenciasMail::class, function (ReporteDiarioIncidenciasMail $mail) use ($capitalHumano) {
        return $mail->hasTo($capitalHumano->email)
            && $mail->fecha === '2026-05-24'
            && $mail->totalIncidencias === 1
            && file_exists($mail->zipPath);
    });
});

test('comando falla si no existe capital humano activo', function () {
    Mail::fake();

    User::factory()->create([
        'rol' => RolUsuario::CapitalHumano,
        'activo' => false,
    ]);

    $this->artisan('incidencias:enviar-reporte-diario', ['--fecha' => '2026-05-24'])
        ->assertExitCode(1);

    Mail::assertNothingQueued();
});
