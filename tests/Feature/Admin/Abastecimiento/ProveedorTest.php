<?php

use App\Models\Proveedor;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access suppliers', function () {
    $this->get(route('admin.abastecimiento.proveedores.index', $this->team))
        ->assertRedirect(route('login'));
});

test('users without supplier permissions are forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.abastecimiento.proveedores.index', $user->currentTeam))
        ->assertForbidden();
});

test('authorized users can register and update suppliers', function () {
    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.proveedores.store', $this->team), [
            'nombre' => 'Distribuidora Central',
            'documento' => '12345678901',
            'contacto' => 'Ana Pérez',
            'email' => 'ventas@example.test',
            'telefono' => '70012345',
            'direccion' => 'Av. Central 100',
            'notas' => 'Entrega semanal',
            'activo' => true,
        ])
        ->assertRedirect(route('admin.abastecimiento.proveedores.index', $this->team));

    $proveedor = Proveedor::query()->where('documento', '12345678901')->firstOrFail();

    $this->assertModelExists($proveedor);

    $this->actingAs($this->user)
        ->put(route('admin.abastecimiento.proveedores.update', [$this->team, $proveedor]), [
            'nombre' => 'Distribuidora Central Actualizada',
            'documento' => '12345678901',
            'contacto' => 'Ana Pérez',
            'email' => 'ventas@example.test',
            'telefono' => '70012345',
            'direccion' => 'Av. Central 100',
            'notas' => null,
            'activo' => true,
        ])
        ->assertRedirect(route('admin.abastecimiento.proveedores.index', $this->team));

    expect($proveedor->refresh()->nombre)->toBe('Distribuidora Central Actualizada');
});

test('supplier document must be unique', function () {
    $proveedor = Proveedor::factory()->create(['documento' => '12345678901']);

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.proveedores.store', $this->team), [
            'nombre' => 'Otro proveedor',
            'documento' => $proveedor->documento,
            'activo' => true,
        ])
        ->assertSessionHasErrors('documento');
});
