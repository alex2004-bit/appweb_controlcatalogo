<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access users', function () {
    $response = $this->get(route('admin.seguridad.users.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list users', function () {
    User::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.seguridad.users.index', $this->team));

    $response->assertOk();
});

test('user can be created with role', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.seguridad.users.store', $this->team), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Vendedor',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

test('user can be updated', function () {
    $targetUser = User::factory()->create(['name' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.seguridad.users.update', [$this->team, $targetUser]), [
            'name' => 'Actualizado',
            'email' => $targetUser->email,
            'role' => 'Admin',
        ]);

    $response->assertRedirect();
    $this->assertEquals('Actualizado', $targetUser->fresh()->name);
});

test('user can be deleted', function () {
    $targetUser = User::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.seguridad.users.destroy', [$this->team, $targetUser]));

    $response->assertRedirect();
    $this->assertModelMissing($targetUser);
});
