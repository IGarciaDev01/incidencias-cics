<?php

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;

test('subdireccion puede consultar auditoria general con filtros acoplados', function () {
    $subdirector = User::factory()->create(['rol' => 'subdirector', 'activo' => true]);
    $actor = User::factory()->create(['rol' => 'capital_humano', 'activo' => true]);
    $incidencia = Incidencia::factory()->create(['folio' => 'INC-AUD-0001']);

    AuditLog::factory()->create([
        'actor_user_id' => $actor->id,
        'action' => AuditAction::IncidenciaAprobada,
        'subject_type' => Incidencia::class,
        'subject_id' => (string) $incidencia->id,
        'incidencia_id' => $incidencia->id,
        'description' => 'Incidencia aprobada para prueba.',
    ]);

    AuditLog::factory()->create([
        'actor_user_id' => $subdirector->id,
        'action' => AuditAction::UsuarioCreado,
        'description' => 'Usuario creado para prueba.',
    ]);

    $this->actingAs($subdirector)
        ->get(route('panel.subdireccion.admin.logs.index', [
            'action' => AuditAction::IncidenciaAprobada->value,
            'actor_user_id' => $actor->id,
            'folio' => 'AUD-0001',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Admin/Logs/Index')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', AuditAction::IncidenciaAprobada->value)
            ->where('logs.data.0.action_label', AuditAction::IncidenciaAprobada->label())
            ->where('logs.data.0.incidencia.folio', 'INC-AUD-0001')
            ->where('logs.data.0.actor.id', $actor->id)
            ->has('acciones')
            ->has('usuarios')
            ->etc()
        );
});

test('roles fuera de subdireccion no pueden consultar auditoria general', function () {
    $capitalHumano = User::factory()->create(['rol' => 'capital_humano', 'activo' => true]);

    $this->actingAs($capitalHumano)
        ->get(route('panel.subdireccion.admin.logs.index'))
        ->assertForbidden();
});

test('las acciones de incidencias generan auditoria sin romper el historial existente', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $jefe = User::factory()->create(['rol' => 'jefe_inmediato', 'activo' => true]);
    $area = crearAreaOperativa(['nombre' => 'Area Auditoria', 'slug' => 'area-auditoria'], $jefe);

    Empleado::create([
        'numero_empleado' => '90001',
        'nombre' => 'Empleado Auditoria',
        'email' => 'auditoria@example.com',
        'tipo' => 'administrativo',
    ]);

    $this->post(route('incidencias.store'), [
        'numero_empleado' => '90001',
        'reportante_nombre' => 'Empleado Auditoria',
        'email_reportante' => 'auditoria@example.com',
        'tipo_empleado' => 'administrativo',
        'area_id' => $area->id,
        'fecha_incidencia' => now()->toDateString(),
        'tipo_incidencia' => 'permiso_economico',
        'descripcion' => 'Solicitud auditada.',
    ])->assertRedirect();

    $incidencia = Incidencia::latest('id')->firstOrFail();

    $this->actingAs($jefe)
        ->post(route('panel.jefe_inmediato.incidencias.aprobar', $incidencia), ['comentario' => 'Aprobada'])
        ->assertRedirect();

    expect($incidencia->historial()->count())->toBe(2)
        ->and(AuditLog::where('incidencia_id', $incidencia->id)->pluck('action')->all())
        ->toContain(AuditAction::IncidenciaCreada, AuditAction::IncidenciaAprobada);
});

test('las acciones administrativas generan auditoria y no guardan password en metadata', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $subdirector = User::factory()->create(['rol' => 'subdirector', 'activo' => true]);

    $this->actingAs($subdirector)
        ->post(route('panel.subdireccion.admin.usuarios.store'), [
            'nombre' => 'Jefe Auditado',
            'email' => 'jefe.auditado@example.com',
            'numero_empleado' => 'JA-001',
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'jefe_inmediato',
            'activo' => true,
            'area_ids' => [],
        ])
        ->assertRedirect();

    $log = AuditLog::where('action', AuditAction::UsuarioCreado)->latest('id')->firstOrFail();

    expect($log->description)->toContain('Jefe Auditado')
        ->and($log->metadata)->not->toHaveKey('password')
        ->and($log->metadata)->not->toHaveKey('password_confirmation');

    $usuario = User::where('email', 'jefe.auditado@example.com')->firstOrFail();

    $this->actingAs($subdirector)
        ->patch(route('panel.subdireccion.admin.usuarios.update', $usuario), [
            'nombre' => 'Jefe Auditado Editado',
            'email' => 'jefe.auditado@example.com',
            'numero_empleado' => 'JA-001',
            'password' => 'password-updated',
            'password_confirmation' => 'password-updated',
            'rol' => 'jefe_inmediato',
            'activo' => true,
            'area_ids' => [],
        ])
        ->assertRedirect();

    $updateLog = AuditLog::where('action', AuditAction::UsuarioActualizado)->latest('id')->firstOrFail();

    expect($updateLog->metadata['cambios'])->not->toHaveKey('password');
});

test('login y logout internos quedan auditados', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $subdirector = User::factory()->create([
        'rol' => 'subdirector',
        'activo' => true,
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'rol' => 'subdirector',
        'password' => 'password',
    ])->assertRedirect();

    $this->post(route('logout'))->assertRedirect();

    expect(AuditLog::where('actor_user_id', $subdirector->id)->pluck('action')->all())
        ->toContain(AuditAction::Login, AuditAction::Logout);
});
