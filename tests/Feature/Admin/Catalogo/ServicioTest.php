<?php

use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access servicios', function () {
    $response = $this->get(route('admin.catalogo.servicios.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list servicios', function () {
    $categoria = Categoria::factory()->create();
    Servicio::factory()->count(3)->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.servicios.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/catalogo/servicios/Index')
        ->has('servicios.data', 3)
    );
});

test('servicio can be created', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.servicios.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Instalación Profesional',
            'precio' => 199.99,
            'activo' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('servicios', ['nombre' => 'Instalación Profesional']);
});

test('servicio can be updated', function () {
    $categoria = Categoria::factory()->create();
    $servicio = Servicio::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.servicios.update', [$this->team, $servicio]), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Actualizado',
            'precio' => 249.99,
            'activo' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('servicios', ['id' => $servicio->id, 'nombre' => 'Actualizado']);
});

test('servicio can be deleted', function () {
    $categoria = Categoria::factory()->create();
    $servicio = Servicio::factory()->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.servicios.destroy', [$this->team, $servicio]));

    $response->assertRedirect();
    $this->assertSoftDeleted('servicios', ['id' => $servicio->id]);
});
