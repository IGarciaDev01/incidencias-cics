<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use Illuminate\Support\Facades\Mail;

test('al crear incidencia requiere tipo_empleado si el empleado no existe', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area X',
        'slug' => 'area-x',
        'descripcion' => null,
        'activa' => true,
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90001',
        'reportante_nombre' => 'Persona X',
        'email_reportante' => 'x@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertSessionHasErrors(['tipo_empleado']);
});

test('tipo_empleado se guarda en empleado y se usa en la incidencia', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area Y',
        'slug' => 'area-y',
        'descripcion' => null,
        'activa' => true,
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

    $area = Area::create([
        'nombre' => 'Area Z',
        'slug' => 'area-z',
        'descripcion' => null,
        'activa' => true,
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
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Test',
    ])->assertRedirect();
});
