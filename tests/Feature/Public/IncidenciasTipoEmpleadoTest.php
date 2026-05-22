<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('al crear incidencia requiere empleado existente en la base de datos', function () {
    Mail::fake();

    $area = crearAreaOperativa([
        'nombre' => 'Area X',
        'slug' => 'area-x',
        'descripcion' => null,
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90001',
        'reportante_nombre' => 'Persona X',
        'email_reportante' => 'x@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertSessionHasErrors(['numero_empleado']);
});

test('tipo_empleado se guarda en empleado y se usa en la incidencia', function () {
    Mail::fake();

    $area = crearAreaOperativa([
        'nombre' => 'Area Y',
        'slug' => 'area-y',
        'descripcion' => null,
    ]);

    Empleado::create([
        'numero_empleado' => '90002',
        'nombre' => 'Persona Y',
        'email' => 'y@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90002',
        'reportante_nombre' => 'Persona Y',
        'email_reportante' => 'y@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertRedirect();

    $empleado = Empleado::where('numero_empleado', '90002')->firstOrFail();
    expect($empleado->tipo->value)->toBe('administrativo');

    $incidencia = Incidencia::where('numero_empleado', '90002')->latest('id')->firstOrFail();
    expect($incidencia->tipo_solicitante->value)->toBe('administrativo');
});

test('si el empleado ya existe con tipo, no requiere tipo_empleado en el request', function () {
    Mail::fake();

    $area = crearAreaOperativa([
        'nombre' => 'Area Z',
        'slug' => 'area-z',
        'descripcion' => null,
    ]);

    Empleado::create([
        'numero_empleado' => '90003',
        'nombre' => 'Persona Z',
        'email' => 'z@example.com',
        'tipo' => 'docente',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90003',
        'reportante_nombre' => 'Persona Z',
        'email_reportante' => 'z@example.com',
        'tipo_empleado' => 'docente',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertRedirect();
});

test('rechaza areas con jefe inactivo aunque mantengan la relacion historica', function () {
    Mail::fake();

    $jefe = User::factory()->create(['rol' => 'jefe_inmediato', 'activo' => false]);
    $area = Area::factory()->create([
        'nombre' => 'Area Jefe Inactivo',
        'slug' => 'area-jefe-inactivo',
        'jefe_id' => $jefe->id,
        'activa' => true,
    ]);
    $area->usuarios()->attach($jefe->id, ['es_jefe' => true]);

    Empleado::create([
        'numero_empleado' => '90005',
        'nombre' => 'Persona Jefe Inactivo',
        'email' => 'jefeinactivo@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90005',
        'reportante_nombre' => 'Persona Jefe Inactivo',
        'email_reportante' => 'jefeinactivo@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertSessionHasErrors(['area_id']);
});

test('no permite registrar incidencias en areas sin jefe asignado', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area Sin Jefe',
        'slug' => 'area-sin-jefe',
        'descripcion' => null,
        'activa' => true,
    ]);

    Empleado::create([
        'numero_empleado' => '90004',
        'nombre' => 'Persona Sin Jefe',
        'email' => 'sinjefe@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90004',
        'reportante_nombre' => 'Persona Sin Jefe',
        'email_reportante' => 'sinjefe@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertSessionHasErrors(['area_id']);
});
