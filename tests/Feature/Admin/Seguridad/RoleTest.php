<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access roles', function () {
    $response = $this->get(route('admin.seguridad.roles.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list roles', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.seguridad.roles.index', $this->team));

    $response->assertOk();
});

test('role can be created with permissions', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.seguridad.roles.store', $this->team), [
            'name' => 'Editor',
            'permissions' => ['view_categorias', 'view_productos'],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('roles', ['name' => 'Editor']);
});

test('role can be updated', function () {
    $role = Role::create(['name' => 'Original', 'guard_name' => 'web']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.seguridad.roles.update', [$this->team, $role]), [
            'name' => 'Actualizado',
            'permissions' => [],
        ]);

    $response->assertRedirect();
    $this->assertEquals('Actualizado', $role->fresh()->name);
});

test('role can be deleted', function () {
    $role = Role::create(['name' => 'Temporal', 'guard_name' => 'web']);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.seguridad.roles.destroy', [$this->team, $role]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});
