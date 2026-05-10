<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use Illuminate\Support\Facades\Mail;

test('buscar empleado autocompleta desde la tabla empleados', function () {
    Empleado::factory()->create([
        'numero_empleado' => '12345',
        'nombre' => 'Juan Perez',
        'email' => 'juan@example.com',
    ]);

    $this->getJson(route('incidencias.buscar-empleado', ['numero' => '123']))
        ->assertSuccessful()
        ->assertJsonFragment([
            'numero_empleado' => '12345',
            'reportante_nombre' => 'Juan Perez',
            'email_reportante' => 'juan@example.com',
        ]);
});

test('al crear incidencia registra el empleado para futuros autocompletados', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Recursos Humanos',
        'slug' => 'recursos-humanos',
        'descripcion' => null,
        'activa' => true,
    ]);

    Empleado::create([
        'numero_empleado' => '77777',
        'nombre' => 'Maria Lopez',
        'email' => 'maria@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '77777',
        'reportante_nombre' => 'Maria Lopez',
        'email_reportante' => 'maria@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Solicitud de permiso.',
    ])->assertRedirect();

    $incidencia = Incidencia::where('numero_empleado', '77777')->latest('id')->first();

    expect($incidencia)->not->toBeNull();
    expect($incidencia->reportante_nombre)->toBe('Maria Lopez');
});
