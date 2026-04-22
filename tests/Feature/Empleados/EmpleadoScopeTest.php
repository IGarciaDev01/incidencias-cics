<?php

use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function crearAreaSimple(string $nombre, string $slug): Area
{
    return Area::create([
        'nombre' => $nombre,
        'slug' => $slug,
        'descripcion' => null,
        'activa' => true,
    ]);
}

function crearEmpleadoConIncidencia(Area $area, string $numero, string $nombre, string $email): void
{
    Empleado::create([
        'numero_empleado' => $numero,
        'nombre' => $nombre,
        'email' => $email,
    ]);

    Incidencia::create([
        'folio' => "INC-TEST-{$numero}",
        'numero_empleado' => $numero,
        'reportante_nombre' => $nombre,
        'email_reportante' => $email,
        'tipo_solicitante' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'minutos_retardo' => null,
        'descripcion' => 'Test',
        'estado' => 'pendiente_jefe',
        'token_seguimiento' => Str::random(64),
    ]);
}

test('jefe ve empleados con incidencias solo de su area', function () {
    $areaA = crearAreaSimple('Area A', 'area-a');
    $areaB = crearAreaSimple('Area B', 'area-b');

    crearEmpleadoConIncidencia($areaA, '11111', 'Empleado A', 'a@example.com');
    crearEmpleadoConIncidencia($areaB, '22222', 'Empleado B', 'b@example.com');

    $jefeA = User::factory()->create([
        'rol' => 'jefe_inmediato',
        'area_id' => $areaA->id,
        'activo' => true,
    ]);

    $this->actingAs($jefeA)
        ->get(route('panel.jefe_inmediato.empleados.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Empleados/Index')
            ->has('empleados.data', 1)
            ->where('empleados.data.0.numero_empleado', '11111'));
});

test('capital humano ve empleados con incidencias de todas las areas', function () {
    $areaA = crearAreaSimple('Area A', 'area-a');
    $areaB = crearAreaSimple('Area B', 'area-b');

    crearEmpleadoConIncidencia($areaA, '11111', 'Empleado A', 'a@example.com');
    crearEmpleadoConIncidencia($areaB, '22222', 'Empleado B', 'b@example.com');
    Empleado::factory()->create([
        'numero_empleado' => '33333',
        'nombre' => 'Empleado Sin Incidencias',
        'email' => 'c@example.com',
    ]);

    $ch = User::factory()->create([
        'rol' => 'capital_humano',
        'area_id' => null,
        'activo' => true,
    ]);

    $this->actingAs($ch)
        ->get(route('panel.capital_humano.empleados.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Empleados/Index')
            ->has('empleados.data', 3));
});
