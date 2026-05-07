<?php

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Services\ValidacionIncidenciaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function crearAreaYOpciones(): array
{
    $area = Area::create([
        'nombre' => 'Area Test',
        'slug' => 'area-test-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    return [
        'area' => $area,
        'dataBase' => [
            'numero_empleado' => '90099',
            'reportante_nombre' => 'Empleado Test',
            'email_reportante' => 'test@example.com',
            'area_id' => $area->id,
            'fecha_incidencia' => Carbon::now()->toDateString(),
            'tipo_incidencia' => 'permiso_economico',
            'descripcion' => 'Test',
        ],
    ];
}

function crearEmpleadoConTipo(string $numero, string $tipo): Empleado
{
    return Empleado::create([
        'numero_empleado' => $numero,
        'nombre' => 'Empleado '.$numero,
        'email' => $numero.'@example.com',
        'tipo' => $tipo,
    ]);
}

function crearIncidencia(array $override = []): Incidencia
{
    $area = Area::create([
        'nombre' => 'Area '.uniqid(),
        'slug' => 'area-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $numero = $override['numero_empleado'] ?? '90100';
    $tipo = $override['tipo'] ?? TipoSolicitante::Docente;
    $fecha = $override['fecha'] ?? Carbon::now();
    $estado = $override['estado'] ?? EstadoIncidencia::PendienteJefe;

    crearEmpleadoConTipo($numero, $tipo->value);

    return Incidencia::create([
        'folio' => 'TEST-'.uniqid(),
        'numero_empleado' => $numero,
        'reportante_nombre' => 'Test',
        'email_reportante' => 'test@example.com',
        'tipo_solicitante' => $tipo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => $fecha->toDateString(),
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 30,
        'descripcion' => 'Test',
        'estado' => $estado->value,
        'token_seguimiento' => Str::random(64),
    ]);
}

// ─── Retardo: entre 11 y 30 minutos ──────────────────────────────────────────

test('se rechaza retardo de 31 o más minutos', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area retardo31',
        'slug' => 'area-retardo31-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90110';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Retardo 31',
        'email' => 'retardo31@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    $response = $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo 31',
        'email_reportante' => 'retardo31@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'retardo',
        'minutos_retardo' => 31,
        'descripcion' => 'Retardo de 31 min',
    ]);

    $response->assertSessionHasErrors('minutos_retardo');
});

test('se permite retardo de 30 minutos', function () {
    Mail::fake();

    $area = Area::create([
        'nombre' => 'Area retardo30',
        'slug' => 'area-retardo30-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90111';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Retardo 30',
        'email' => 'retardo30@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo 30',
        'email_reportante' => 'retardo30@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'retardo',
        'minutos_retardo' => 30,
        'descripcion' => 'Retardo de 30 min',
    ]);

    $aprobadas = Incidencia::where('numero_empleado', $empleadoNum)
        ->whereIn('estado', [
            EstadoIncidencia::PendienteJefe->value,
            EstadoIncidencia::PendienteCapitalHumano->value,
            EstadoIncidencia::PendienteSubdireccion->value,
            EstadoIncidencia::Aprobada->value,
        ])
        ->where('tipo_incidencia', TipoIncidencia::Retardo->value)
        ->count();

    expect($aprobadas)->toBeGreaterThan(0);
});

// ─── Retardo: máximo 2 por quincena ──────────────────────────────────────────

test('se rechazan retardos cuando ya hay 2 en la quincena', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area retardo2',
        'slug' => 'area-retardo2-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90112';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Retardo Many',
        'email' => 'retardomany@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Many',
        'email_reportante' => 'retardomany@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-10',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 15,
        'descripcion' => 'Retardo 1',
        'estado' => EstadoIncidencia::PendienteJefe->value,
        'token_seguimiento' => Str::random(64),
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Many',
        'email_reportante' => 'retardomany@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-12',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 20,
        'descripcion' => 'Retardo 2',
        'estado' => EstadoIncidencia::Aprobada->value,
        'token_seguimiento' => Str::random(64),
    ]);

    $response = $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Many',
        'email_reportante' => 'retardomany@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'retardo',
        'minutos_retardo' => 15,
        'descripcion' => 'Retardo 3',
    ]);

    $response->assertRedirect();
    expect(session('error'))->toContain('2 retardos por quincena');

    Carbon::setTestNow();
});

test('se permite un tercer retardo en la siguiente quincena', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-20'));

    $area = Area::create([
        'nombre' => 'Area retardoq2',
        'slug' => 'area-retardoq2-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90113';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Retardo Quincena 2',
        'email' => 'retardoq2@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Quincena 2',
        'email_reportante' => 'retardoq2@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-10',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 10,
        'descripcion' => 'Retardo 1',
        'estado' => EstadoIncidencia::PendienteJefe->value,
        'token_seguimiento' => Str::random(64),
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Quincena 2',
        'email_reportante' => 'retardoq2@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-12',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 15,
        'descripcion' => 'Retardo 2',
        'estado' => EstadoIncidencia::Aprobada->value,
        'token_seguimiento' => Str::random(64),
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Retardo Quincena 2',
        'email_reportante' => 'retardoq2@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-20',
        'tipo_incidencia' => 'retardo',
        'minutos_retardo' => 15,
        'descripcion' => 'Retardo 3 en segunda quincena',
    ]);

    $aprobadas = Incidencia::where('numero_empleado', $empleadoNum)
        ->whereIn('estado', [
            EstadoIncidencia::PendienteJefe->value,
            EstadoIncidencia::PendienteCapitalHumano->value,
            EstadoIncidencia::PendienteSubdireccion->value,
            EstadoIncidencia::Aprobada->value,
        ])
        ->where('tipo_incidencia', TipoIncidencia::Retardo->value)
        ->where('fecha_incidencia', '>=', '2026-04-16')
        ->count();

    expect($aprobadas)->toBeGreaterThan(0);

    Carbon::setTestNow();
});

// ─── Permiso Económico: máximo 3 por mes ─────────────────────────────────────

test('se rechaza permiso economico cuando ya hay 3 en el mes', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area pe3',
        'slug' => 'area-pe3-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90114';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'PermisoEconomico 3',
        'email' => 'pe3@example.com',
        'tipo' => TipoSolicitante::Docente->value,
    ]);

    for ($i = 0; $i < 3; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'PermisoEconomico 3',
            'email_reportante' => 'pe3@example.com',
            'tipo_solicitante' => TipoSolicitante::Docente->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico->value,
            'minutos_retardo' => null,
            'descripcion' => 'Permiso '.$i,
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $response = $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'PermisoEconomico 3',
        'email_reportante' => 'pe3@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Permiso 4',
    ]);

    $response->assertRedirect();
    expect(session('error'))->toContain('3 permisos económicos');

    Carbon::setTestNow();
});

// ─── Otros tipos de incidencia sin límite ───────────────────────────────────

test('puede registrar muchos permisos sindicales sin limite', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area ps',
        'slug' => 'area-ps-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90115';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Permiso Sindical',
        'email' => 'ps@example.com',
        'tipo' => TipoSolicitante::Docente->value,
    ]);

    for ($i = 0; $i < 20; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'Permiso Sindical',
            'email_reportante' => 'ps@example.com',
            'tipo_solicitante' => TipoSolicitante::Docente->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoSindical->value,
            'minutos_retardo' => null,
            'descripcion' => 'Permiso Sindical '.$i,
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Permiso Sindical',
        'email_reportante' => 'ps@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'permiso_sindical',
        'descripcion' => 'Permiso Sindical 21',
    ]);

    $rechazadasCount = Incidencia::where('numero_empleado', $empleadoNum)
        ->where('estado', EstadoIncidencia::Rechazada->value)
        ->where('tipo_incidencia', TipoIncidencia::PermisoSindical->value)
        ->count();

    expect($rechazadasCount)->toBe(0);

    Carbon::setTestNow();
});

// ─── Incidencias rechazadas no cuentan para el límite ────────────────────────

test('incidencias rechazadas no cuentan para el limite de permiso economico', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area norechazadas',
        'slug' => 'area-norechazadas-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90116';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'No Rech',
        'email' => 'norech@example.com',
        'tipo' => TipoSolicitante::Docente->value,
    ]);

    for ($i = 0; $i < 12; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'No Rech',
            'email_reportante' => 'norech@example.com',
            'tipo_solicitante' => TipoSolicitante::Docente->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico->value,
            'minutos_retardo' => null,
            'descripcion' => 'Incidencia '.$i,
            'estado' => EstadoIncidencia::Rechazada->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $service = app(ValidacionIncidenciaService::class);
    $fecha = Carbon::parse('2026-04-15');

    expect($service->excedeLimitePermisoEconomicoMensual($empleadoNum, $fecha))->toBeFalse();

    Carbon::setTestNow();
});
