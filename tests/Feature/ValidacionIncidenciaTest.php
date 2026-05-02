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

dataset('tipos_solicitante', [
    'docente' => [TipoSolicitante::Docente],
    'administrativo' => [TipoSolicitante::Administrativo],
]);

dataset('limites_mensuales', [
    'docente' => [TipoSolicitante::Docente, 12],
    'administrativo' => [TipoSolicitante::Administrativo, 12],
]);

// ─── Límite mensual ──────────────────────────────────────────────────────────

test('docente recibe rechazo automatico al alcanzar limite mensual de 12 incidencias', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area docente',
        'slug' => 'area-docente-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90101';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Docente Test',
        'email' => 'docente@example.com',
        'tipo' => TipoSolicitante::Docente->value,
    ]);

    for ($i = 0; $i < 12; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'Docente Test',
            'email_reportante' => 'docente@example.com',
            'tipo_solicitante' => TipoSolicitante::Docente->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico->value,
            'minutos_retardo' => null,
            'descripcion' => 'Incidencia '.$i,
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Docente Test',
        'email_reportante' => 'docente@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Incidencia 13',
    ]);

    $rechazadasCount = Incidencia::where('numero_empleado', $empleadoNum)
        ->where('estado', EstadoIncidencia::Rechazada->value)
        ->count();

    expect($rechazadasCount)->toBeGreaterThan(0);
    expect($this->app['session.store']->get('error'))->toContain('límite mensual');

    Carbon::setTestNow();
});

test('administrativo puede registrar hasta 12 incidencias mensuales', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area administrativo',
        'slug' => 'area-administrativo-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90102';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Administrativo Test',
        'email' => 'admin@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    for ($i = 0; $i < 12; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'Administrativo Test',
            'email_reportante' => 'admin@example.com',
            'tipo_solicitante' => TipoSolicitante::Administrativo->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico->value,
            'minutos_retardo' => null,
            'descripcion' => 'Incidencia '.$i,
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Administrativo Test',
        'email_reportante' => 'admin@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Incidencia 13',
    ]);

    $rechazadasCount = Incidencia::where('numero_empleado', $empleadoNum)
        ->where('estado', EstadoIncidencia::Rechazada->value)
        ->count();

    expect($rechazadasCount)->toBeGreaterThan(0);

    Carbon::setTestNow();
});

// ─── Límite semanal de retardos ───────────────────────────────────────────────

test('se rechaza creacion si minutos de retardo exceden 2 horas semanales', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15 Tuesday'));

    $area = Area::create([
        'nombre' => 'Area retardo',
        'slug' => 'area-retardo-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90103';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Empleado Retardo',
        'email' => 'retardo@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Empleado Retardo',
        'email_reportante' => 'retardo@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-14',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 90,
        'descripcion' => 'Retardo 1',
        'estado' => EstadoIncidencia::PendienteJefe->value,
        'token_seguimiento' => Str::random(64),
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Empleado Retardo',
        'email_reportante' => 'retardo@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 40,
        'descripcion' => 'Retardo 2',
        'estado' => EstadoIncidencia::PendienteJefe->value,
        'token_seguimiento' => Str::random(64),
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Empleado Retardo',
        'email_reportante' => 'retardo@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'retardo',
        'minutos_retardo' => 30,
        'descripcion' => 'Retardo 3',
    ]);

    $rechazadas = Incidencia::where('numero_empleado', $empleadoNum)
        ->where('estado', EstadoIncidencia::Rechazada->value)
        ->get();

    expect($rechazadas->count())->toBeGreaterThan(0);
    expect($rechazadas->first()->motivo_rechazo)->toContain('quincena');

    Carbon::setTestNow();
});

test('incidencia rechazada por validacion queda registrada como Rechazada en la base de datos', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area limite',
        'slug' => 'area-limite-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90104';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Empleado Limite',
        'email' => 'limite@example.com',
        'tipo' => TipoSolicitante::Docente->value,
    ]);

    for ($i = 0; $i < 12; $i++) {
        Incidencia::create([
            'folio' => 'FOLIO-'.uniqid(),
            'numero_empleado' => $empleadoNum,
            'reportante_nombre' => 'Empleado Limite',
            'email_reportante' => 'limite@example.com',
            'tipo_solicitante' => TipoSolicitante::Docente->value,
            'area_id' => $area->id,
            'fecha_incidencia' => '2026-04-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico->value,
            'minutos_retardo' => null,
            'descripcion' => 'Incidencia '.$i,
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'token_seguimiento' => Str::random(64),
        ]);
    }

    $this->post(route('incidencias.store'), [
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Empleado Limite',
        'email_reportante' => 'limite@example.com',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-15',
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Incidencia 13',
    ]);

    $incidenciaRechazada = Incidencia::where('numero_empleado', $empleadoNum)
        ->where('estado', EstadoIncidencia::Rechazada->value)
        ->whereNotNull('motivo_rechazo')
        ->first();

    expect($incidenciaRechazada)->not->toBeNull();
    expect($incidenciaRechazada->motivo_rechazo)->toContain('límite mensual');

    Carbon::setTestNow();
});

test('ValidacionIncidenciaService excedeLimiteQuincenalRetardos retorna true cuando se excede el limite', function () {
    $service = app(ValidacionIncidenciaService::class);

    $area = Area::create([
        'nombre' => 'Area semtest',
        'slug' => 'area-semtest-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90105';
    Empleado::create([
        'numero_empleado' => $empleadoNum,
        'nombre' => 'Sem Test',
        'email' => 'sem@example.com',
        'tipo' => TipoSolicitante::Administrativo->value,
    ]);

    Incidencia::create([
        'folio' => 'FOLIO-'.uniqid(),
        'numero_empleado' => $empleadoNum,
        'reportante_nombre' => 'Sem Test',
        'email_reportante' => 'sem@example.com',
        'tipo_solicitante' => TipoSolicitante::Administrativo->value,
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-04-13',
        'tipo_incidencia' => TipoIncidencia::Retardo->value,
        'minutos_retardo' => 60,
        'descripcion' => 'Retardo',
        'estado' => EstadoIncidencia::Aprobada->value,
        'token_seguimiento' => Str::random(64),
    ]);

    $fecha = Carbon::parse('2026-04-15');
    expect($service->excedeLimiteQuincenalRetardos($empleadoNum, $fecha, 70))->toBeTrue();
    expect($service->excedeLimiteQuincenalRetardos($empleadoNum, $fecha, 60))->toBeFalse();
});

test('incidencias rechazadas no se cuentan para el limite mensual', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $area = Area::create([
        'nombre' => 'Area norechazadas',
        'slug' => 'area-norechazadas-'.uniqid(),
        'descripcion' => null,
        'activa' => true,
    ]);

    $empleadoNum = '90106';
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

    expect($service->excedeLimiteMensual($empleadoNum, TipoSolicitante::Docente, $fecha))->toBeFalse();

    Carbon::setTestNow();
});
