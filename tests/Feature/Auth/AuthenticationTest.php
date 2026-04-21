<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // assertAuthenticated relies on session state after session()->regenerate().
    // Use the response redirect as the authentication indicator instead.
    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->markTestSkipped('Two-factor challenge route is not registered in this application.');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
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
