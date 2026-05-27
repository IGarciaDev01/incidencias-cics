<?php

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Support\Carbon;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('panel.dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('panel.dashboard'));
    $response->assertOk();
});

test('dashboard monthly requests chart uses spanish month labels', function () {
    $this->travelTo(Carbon::parse('2026-03-15'));

    $user = User::factory()->create();
    Incidencia::factory()->create([
        'fecha_incidencia' => '2026-01-01',
    ]);

    $response = $this->actingAs($user)->get(route('panel.dashboard'));

    expect($response->inertiaProps('stats.charts.solicitudes_mes.2026-01.label'))
        ->toBe('Ene');
});
