<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access reportes', function () {
    $response = $this->get(route('admin.finanzas.reportes.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('reportes page renders', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.finanzas.reportes.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/finanzas/reportes/Index')
        ->has('resumen')
        ->has('ingresosPorMes', 12)
    );
});
