<?php

use App\Enums\RolUsuario;
use App\Models\Area;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'rol' => RolUsuario::Subdirector,
    ]);

    $response = $this->post(route('login'), [
        'numero_empleado' => $user->numero_empleado,
        'rol' => 'subdirector',
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('login ignores stale intended urls that could cause 403', function () {
    $area = Area::create([
        'nombre' => 'Area A',
        'slug' => 'area-a',
        'descripcion' => null,
        'activa' => true,
    ]);

    $user = User::factory()->create([
        'rol' => RolUsuario::JefeInmediato,
        'activo' => true,
    ]);

    $user->areas()->attach($area->id, ['es_jefe' => true]);

    $response = $this
        ->withSession(['url.intended' => '/panel/subdireccion/incidencias'])
        ->post(route('login'), [
            'rol' => 'jefe_inmediato',
            'area_id' => $area->id,
            'password' => 'password',
        ]);

    $response->assertRedirect(route('panel.jefe_inmediato.incidencias.index'));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->markTestSkipped('Two-factor challenge route is not registered in this application.');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'rol' => RolUsuario::Subdirector,
    ]);

    $this->post(route('login'), [
        'numero_empleado' => $user->numero_empleado,
        'rol' => 'subdirector',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('users are rate limited', function () {
    $this->markTestSkipped('Rate limiting is not applied to the custom login route.');
});
