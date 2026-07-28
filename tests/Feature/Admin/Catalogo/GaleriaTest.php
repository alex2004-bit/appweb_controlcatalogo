<?php

use App\Models\Galeria;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access galeria', function () {
    $response = $this->get(route('admin.catalogo.galeria.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list galeria', function () {
    Galeria::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.galeria.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/catalogo/galeria/Index')
        ->has('imagenes.data', 3)
    );
});

test('galeria item can be updated', function () {
    $item = Galeria::factory()->create(['titulo' => 'Original', 'activo' => true]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.galeria.update', [$this->team, $item]), [
            'titulo' => 'Actualizado',
            'tipo' => 'imagen',
            'activo' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('galeria', ['id' => $item->id, 'titulo' => 'Actualizado']);
});

test('galeria item can be deleted', function () {
    $item = Galeria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.galeria.destroy', [$this->team, $item]));

    $response->assertRedirect();
    $this->assertSoftDeleted('galeria', ['id' => $item->id]);
});
