<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access empresa config', function () {
    $response = $this->get(route('admin.configuracion.empresa.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view empresa config', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.configuracion.empresa.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/configuracion/Empresa')
        ->has('config')
    );
});

test('empresa config can be updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.empresa.update', $this->team), [
            'nombre_empresa' => 'Mi Empresa SAC',
            'ruc' => '12345678901',
            'direccion' => 'Av. Principal 123',
            'telefono' => '999888777',
            'email' => 'info@miempresa.com',
            'website' => 'https://miempresa.com',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('configuraciones', ['nombre_empresa' => 'Mi Empresa SAC']);
});
