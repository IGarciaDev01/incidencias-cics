<?php

use App\Models\Incidencia;
use Inertia\Testing\AssertableInertia as Assert;

test('confirmacion de incidencia requiere sesion del folio recien creado', function () {
    $incidencia = Incidencia::factory()->create([
        'folio' => 'INC-2026-9001',
    ]);

    $this->get(route('incidencias.confirmacion', $incidencia->folio))
        ->assertForbidden();
});

test('confirmacion solo muestra informacion informativa del folio', function () {
    $incidencia = Incidencia::factory()->create([
        'folio' => 'INC-2026-9002',
        'numero_empleado' => '55555',
    ]);

    $this->withSession(['seguimiento_verificado' => $incidencia->folio])
        ->get(route('incidencias.confirmacion', $incidencia->folio))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Incidencias/Confirmacion')
            ->where('folio', $incidencia->folio)
            ->missing('numero_empleado')
            ->missing('tipo_incidencia')
            ->missing('fecha_incidencia')
            ->missing('hora_incidencia'));
});

test('empleado puede descargar comprobante pdf con folio verificado', function () {
    $incidencia = Incidencia::factory()->create([
        'folio' => 'INC-2026-9003',
        'numero_empleado' => '55556',
        'reportante_nombre' => 'Empleado PDF',
        'email_reportante' => 'empleado.pdf@example.com',
    ]);

    $response = $this->withSession(['seguimiento_verificado' => $incidencia->folio])
        ->get(route('comprobante.descargar', $incidencia->folio));

    $response->assertOk();

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->getContent())->toStartWith('%PDF');
});
