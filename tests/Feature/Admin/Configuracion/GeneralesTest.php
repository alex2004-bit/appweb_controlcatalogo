<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access generales config', function () {
    $response = $this->get(route('admin.configuracion.generales.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view generales config', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.configuracion.generales.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/configuracion/Generales'));
});

test('generales config can be updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.generales.update', $this->team), [
            'moneda' => 'USD',
            'impuesto' => 18,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('configuraciones', ['moneda' => 'USD', 'impuesto' => 18]);
});

test('guests cannot access social config', function () {
    $response = $this->get(route('admin.configuracion.social.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('social config can be viewed and updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.configuracion.social.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/configuracion/Social'));

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.social.update', $this->team), [
            'whatsapp' => '+51999888777',
            'facebook' => 'https://facebook.com/miempresa',
            'instagram' => '@miempresa',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('configuraciones', ['whatsapp' => '+51999888777']);
});
