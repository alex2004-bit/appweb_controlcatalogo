<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;

    $this->user->assignRole('Super Admin');
});

test('guests cannot access productos', function () {
    $response = $this->get(route('admin.catalogo.productos.index', $this->team));

    $response->assertRedirect(route('login'));
});

test('authenticated users can list productos', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->count(3)->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.productos.index', $this->team));

    $response->assertOk();
});

test('producto can be created', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Cámara IP 4K',
            'precio' => 299.99,
            'sku' => 'CAM-4K-001',
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Cámara IP 4K',
        'sku' => 'CAM-4K-001',
    ]);
});

test('producto can be updated', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Actualizado',
            'precio' => 199.99,
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Actualizado',
    ]);
});

test('producto can be deleted', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.productos.destroy', [$this->team, $producto]));

    $response->assertRedirect();

    $this->assertSoftDeleted('productos', [
        'id' => $producto->id,
    ]);
});

test('producto creation requires nombre', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => null,
            'precio' => 100,
        ]);

    $response->assertSessionHasErrors(['nombre', 'categoria_id']);
});
