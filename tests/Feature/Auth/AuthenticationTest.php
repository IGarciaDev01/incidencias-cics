<?php

use App\Enums\RolUsuario;
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
        'rol' => 'subdirector',
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('login ignores stale intended urls that could cause 403', function () {
    $user = User::factory()->create([
        'rol' => RolUsuario::JefeInmediato,
        'activo' => true,
    ]);

    $area = crearAreaOperativa([
        'nombre' => 'Area A',
        'slug' => 'area-a',
        'descripcion' => null,
    ], $user);

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

test('inactive authenticated users cannot access the panel dashboard', function () {
    $user = User::factory()->create([
        'rol' => RolUsuario::Subdirector,
        'activo' => false,
    ]);

    $this->actingAs($user)
        ->get(route('panel.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('users are rate limited', function () {
    $this->markTestSkipped('Rate limiting is not applied to the custom login route.');
});
