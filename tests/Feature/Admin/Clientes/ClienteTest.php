<?php

use App\Models\Cliente;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;

    $this->user->assignRole('Super Admin');
});

test('guests cannot access clientes', function () {
    $response = $this->get(route('admin.clientes.index', $this->team));

    $response->assertRedirect(route('login'));
});

test('authenticated users can list clientes', function () {
    Cliente::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.clientes.index', $this->team));

    $response->assertOk();
});

test('cliente can be created', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.clientes.store', $this->team), [
            'tipo_persona' => 'natural',
            'nombre' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'telefono' => '123456789',
            'documento' => '12345678',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@example.com',
    ]);
});

test('cliente can be updated', function () {
    $cliente = Cliente::factory()->create(['nombre' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.clientes.update', [$this->team, $cliente]), [
            'tipo_persona' => 'juridica',
            'nombre' => 'Actualizado',
            'empresa' => 'Empresa SAC',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'nombre' => 'Actualizado',
    ]);
});

test('cliente can be deleted', function () {
    $cliente = Cliente::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.clientes.destroy', [$this->team, $cliente]));

    $response->assertRedirect();

    $this->assertSoftDeleted('clientes', [
        'id' => $cliente->id,
    ]);
});

test('cliente creation requires tipo_persona and nombre', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.clientes.store', $this->team), []);

    $response->assertSessionHasErrors(['tipo_persona', 'nombre']);
});
