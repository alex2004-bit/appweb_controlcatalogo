<?php

use App\Models\Categoria;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;

    $this->user->assignRole('Super Admin');
});

test('guests cannot access categorias index', function () {
    $response = $this->get(route('admin.catalogo.categorias.index', $this->team));

    $response->assertRedirect(route('login'));
});

test('authenticated users can list categorias', function () {
    Categoria::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.categorias.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/catalogo/categorias/Index')
        ->has('categorias.data', 3)
    );
});

test('categoria can be created', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.categorias.store', $this->team), [
            'nombre' => 'Cámaras IP',
            'descripcion' => 'Categoría de cámaras de seguridad IP',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('categorias', [
        'nombre' => 'Cámaras IP',
    ]);
});

test('categoria can be updated', function () {
    $categoria = Categoria::factory()->create(['nombre' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.categorias.update', [$this->team, $categoria]), [
            'nombre' => 'Actualizada',
            'descripcion' => 'Descripción actualizada',
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('categorias', [
        'id' => $categoria->id,
        'nombre' => 'Actualizada',
    ]);
});

test('categoria can be deleted', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.categorias.destroy', [$this->team, $categoria]));

    $response->assertRedirect();

    $this->assertSoftDeleted('categorias', [
        'id' => $categoria->id,
    ]);
});

test('categorias can be searched', function () {
    Categoria::factory()->create(['nombre' => 'Cámaras IP']);
    Categoria::factory()->create(['nombre' => 'Alarmas']);
    Categoria::factory()->create(['nombre' => 'Accesorios']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.categorias.index', [$this->team, 'search' => 'Cámaras']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('categorias.data', 1)
    );
});
