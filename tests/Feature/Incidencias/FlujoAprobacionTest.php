<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;

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
