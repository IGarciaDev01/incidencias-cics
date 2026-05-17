<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('el jefe inmediato solo puede acceder a incidencias de su area', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $areaA = Area::create([
        'nombre' => 'Area A',
        'slug' => 'area-a',
        'descripcion' => null,
        'activa' => true,
    ]);

    $areaB = Area::create([
        'nombre' => 'Area B',
        'slug' => 'area-b',
        'descripcion' => null,
        'activa' => true,
    ]);

    Empleado::create([
        'numero_empleado' => '20001',
        'nombre' => 'Empleado Prueba',
        'email' => 'empleado@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '20001',
        'reportante_nombre' => 'Empleado Prueba',
        'email_reportante' => 'empleado@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $areaA->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Solicitud de permiso.',
    ])->assertRedirect();

    $incidencia = Incidencia::latest('id')->firstOrFail();

    $jefeB = User::factory()->create([
        'rol' => 'jefe_inmediato',
        'activo' => true,
    ]);
    $jefeB->areas()->attach($areaB->id, ['es_jefe' => true]);

    $this->actingAs($jefeB)
        ->get(route('panel.jefe_inmediato.incidencias.show', $incidencia))
        ->assertForbidden();

    $this->actingAs($jefeB)
        ->post(route('panel.jefe_inmediato.incidencias.aprobar', $incidencia), ['comentario' => 'OK'])
        ->assertForbidden();
});

test('flujo de aprobacion es jefe -> capital humano -> subdireccion', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $area = Area::create([
        'nombre' => 'Area Flujo',
        'slug' => 'area-flujo',
        'descripcion' => null,
        'activa' => true,
    ]);

    Empleado::create([
        'numero_empleado' => '30001',
        'nombre' => 'Empleado Prueba',
        'email' => 'empleado@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '30001',
        'reportante_nombre' => 'Empleado Prueba',
        'email_reportante' => 'empleado@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Solicitud de permiso.',
    ])->assertRedirect();

    $incidencia = Incidencia::latest('id')->firstOrFail();

    $jefe = User::factory()->create([
        'rol' => 'jefe_inmediato',
        'activo' => true,
    ]);
    $jefe->areas()->attach($area->id, ['es_jefe' => true]);

    $capitalHumano = User::factory()->create([
        'rol' => 'capital_humano',
        'activo' => true,
    ]);

    $subdireccion = User::factory()->create([
        'rol' => 'subdirector',
        'activo' => true,
    ]);

    $this->actingAs($jefe)
        ->post(route('panel.jefe_inmediato.incidencias.aprobar', $incidencia), ['comentario' => 'Escalar a CH'])
        ->assertRedirect();

    $incidencia->refresh();
    expect($incidencia->estado->value)->toBe('pendiente_capital_humano');

    $this->actingAs($capitalHumano)
        ->post(route('panel.capital_humano.incidencias.aprobar', $incidencia), ['comentario' => 'Aprobado CH'])
        ->assertRedirect();

    $incidencia->refresh();
    expect($incidencia->estado->value)->toBe('pendiente_subdireccion');

    $this->actingAs($subdireccion)
        ->post(route('panel.subdireccion.incidencias.aprobar', $incidencia), ['comentario' => 'Aprobacion final'])
        ->assertRedirect();

    $incidencia->refresh();
    expect($incidencia->estado->value)->toBe('aprobada');
});

test('capital humano puede enviar una incidencia pendiente a sindicato', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $capitalHumano = User::factory()->create([
        'rol' => 'capital_humano',
        'activo' => true,
    ]);

    $incidencia = Incidencia::factory()->estadoCapitalHumano()->create();

    $this->actingAs($capitalHumano)
        ->post(route('panel.capital_humano.incidencias.enviar-sindicato', $incidencia), [
            'comentario' => 'Requiere validación del sindicato.',
        ])
        ->assertRedirect();

    $incidencia->refresh();

    expect($incidencia->estado->value)->toBe('pendiente_sindicato')
        ->and($incidencia->enviado_sindicato_at)->not->toBeNull()
        ->and($incidencia->revisado_por)->toBe($capitalHumano->id);
});

test('sindicato solo ve incidencias enviadas por capital humano', function () {
    $sindicato = User::factory()->create([
        'rol' => 'sindicato',
        'activo' => true,
    ]);

    $enviada = Incidencia::factory()->estadoSindicato()->create([
        'folio' => 'INC-SIND-0001',
    ]);
    $noEnviada = Incidencia::factory()->estadoCapitalHumano()->create([
        'folio' => 'INC-SIND-0002',
    ]);

    $this->actingAs($sindicato)
        ->get(route('panel.sindicato.incidencias.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Sindicato/Incidencias/Index')
            ->has('incidencias.data', 1)
            ->where('incidencias.data.0.id', $enviada->id));

    $this->actingAs($sindicato)
        ->get(route('panel.sindicato.incidencias.show', $noEnviada))
        ->assertForbidden();
});

test('sindicato solo puede resolver incidencias pendientes de sindicato', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $sindicato = User::factory()->create([
        'rol' => 'sindicato',
        'activo' => true,
    ]);

    $enviada = Incidencia::factory()->estadoSindicato()->create();
    $noEnviada = Incidencia::factory()->estadoCapitalHumano()->create();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.aprobar', $noEnviada), ['comentario' => 'No permitido'])
        ->assertUnprocessable();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.aprobar', $enviada), ['comentario' => 'Aprobación final'])
        ->assertRedirect();

    $enviada->refresh();

    expect($enviada->estado->value)->toBe('aprobada')
        ->and($enviada->revisado_por)->toBe($sindicato->id);
});
