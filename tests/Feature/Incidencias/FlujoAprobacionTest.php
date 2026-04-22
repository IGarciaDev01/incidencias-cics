<?php

use App\Models\Area;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('el jefe inmediato solo puede acceder a incidencias de su area', function () {
    Mail::fake();

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
        'area_id' => $areaB->id,
        'activo' => true,
    ]);

    $this->actingAs($jefeB)
        ->get(route('panel.jefe_inmediato.incidencias.show', $incidencia))
        ->assertForbidden();

    $this->actingAs($jefeB)
        ->post(route('panel.jefe_inmediato.incidencias.aprobar', $incidencia), ['comentario' => 'OK'])
        ->assertForbidden();
});

test('flujo de aprobacion es jefe -> capital humano -> subdireccion', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area Flujo',
        'slug' => 'area-flujo',
        'descripcion' => null,
        'activa' => true,
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
        'area_id' => $area->id,
        'activo' => true,
    ]);

    $capitalHumano = User::factory()->create([
        'rol' => 'capital_humano',
        'area_id' => null,
        'activo' => true,
    ]);

    $subdireccion = User::factory()->create([
        'rol' => 'subdireccion_academica',
        'area_id' => null,
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
