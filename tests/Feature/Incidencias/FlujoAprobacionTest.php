<?php

use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('el jefe inmediato solo puede acceder a incidencias de su area', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $areaA = crearAreaOperativa([
        'nombre' => 'Area A',
        'slug' => 'area-a',
        'descripcion' => null,
    ]);

    $jefeB = User::factory()->create([
        'rol' => 'jefe_inmediato',
        'activo' => true,
    ]);

    $areaB = crearAreaOperativa([
        'nombre' => 'Area B',
        'slug' => 'area-b',
        'descripcion' => null,
    ], $jefeB);

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

    $this->actingAs($jefeB)
        ->get(route('panel.jefe_inmediato.incidencias.show', $incidencia))
        ->assertForbidden();

    $this->actingAs($jefeB)
        ->get(route('panel.jefe_inmediato.incidencias.index', ['area' => $areaA->id]))
        ->assertForbidden();

    $this->actingAs($jefeB)
        ->post(route('panel.jefe_inmediato.incidencias.aprobar', $incidencia), ['comentario' => 'OK'])
        ->assertForbidden();
});

test('flujo de aprobacion es jefe -> capital humano -> subdireccion', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $jefe = User::factory()->create([
        'rol' => 'jefe_inmediato',
        'activo' => true,
    ]);

    $area = crearAreaOperativa([
        'nombre' => 'Area Flujo',
        'slug' => 'area-flujo',
        'descripcion' => null,
    ], $jefe);

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

test('capital humano ya no tiene ruta para enviar incidencias a sindicato', function () {
    expect(Route::has('panel.capital_humano.incidencias.enviar-sindicato'))->toBeFalse();
});

test('sindicato ve todas las incidencias del sistema', function () {
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
            ->has('incidencias.data', 2)
            ->where('incidencias.data.0.id', $enviada->id)
            ->where('incidencias.data.1.id', $noEnviada->id));

    $this->actingAs($sindicato)
        ->get(route('panel.sindicato.incidencias.show', $noEnviada))
        ->assertOk();
});

test('sindicato puede resolver incidencias en cualquier etapa activa', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $sindicato = User::factory()->create([
        'rol' => 'sindicato',
        'activo' => true,
    ]);

    $pendienteJefe = Incidencia::factory()->estadoJefe()->create();
    $pendienteCapitalHumano = Incidencia::factory()->estadoCapitalHumano()->create();
    $pendienteSubdireccion = Incidencia::factory()->estadoSubdireccion()->create();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.aprobar', $pendienteJefe), ['comentario' => 'Aprobación final'])
        ->assertRedirect();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.aprobar', $pendienteCapitalHumano), ['comentario' => 'Aprobación final'])
        ->assertRedirect();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.rechazar', $pendienteSubdireccion), ['motivo' => 'Rechazo definitivo por Sindicato.'])
        ->assertRedirect();

    $pendienteJefe->refresh();
    $pendienteCapitalHumano->refresh();
    $pendienteSubdireccion->refresh();

    expect($pendienteJefe->estado->value)->toBe('aprobada')
        ->and($pendienteJefe->revisado_por)->toBe($sindicato->id)
        ->and($pendienteCapitalHumano->estado->value)->toBe('aprobada')
        ->and($pendienteCapitalHumano->revisado_por)->toBe($sindicato->id)
        ->and($pendienteSubdireccion->estado->value)->toBe('rechazada')
        ->and($pendienteSubdireccion->revisado_por)->toBe($sindicato->id);
});

test('sindicato no puede resolver incidencias finalizadas', function () {
    Mail::fake();
    $this->withoutMiddleware(PreventRequestForgery::class);

    $sindicato = User::factory()->create([
        'rol' => 'sindicato',
        'activo' => true,
    ]);

    $aprobada = Incidencia::factory()->aprobada()->create();
    $rechazada = Incidencia::factory()->rechazada()->create();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.aprobar', $aprobada), ['comentario' => 'No permitido'])
        ->assertUnprocessable();

    $this->actingAs($sindicato)
        ->post(route('panel.sindicato.incidencias.rechazar', $rechazada), ['motivo' => 'No permitido por estado final.'])
        ->assertUnprocessable();
});
