<?php

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access cotizaciones', function () {
    $response = $this->get(route('admin.cotizaciones.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list cotizaciones', function () {
    Cotizacion::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.cotizaciones.index', $this->team));

    $response->assertOk();
});

test('cotizacion can be created with items', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.cotizaciones.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 2,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cotizaciones', ['cliente_id' => $cliente->id]);
    $this->assertDatabaseHas('detalle_cotizacion', ['item_nombre' => $producto->nombre]);
});

test('cotizacion can be shown', function () {
    $cotizacion = Cotizacion::factory()->create(['created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.cotizaciones.show', [$this->team, $cotizacion]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/cotizaciones/Show')
    );
});

test('cotizacion can be updated', function () {
    $cliente = Cliente::factory()->create();
    $cotizacion = Cotizacion::factory()->create([
        'cliente_id' => $cliente->id,
        'created_by' => $this->user->id,
        'estado' => 'borrador',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.cotizaciones.update', [$this->team, $cotizacion]), [
            'cliente_id' => $cliente->id,
            'estado' => 'aprobada',
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => 0,
                    'item_nombre' => 'Item test',
                    'cantidad' => 1,
                    'precio_unitario' => 50,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();
    $this->assertEquals('aprobada', $cotizacion->fresh()->estado);
});

test('cotizacion can be deleted', function () {
    $cotizacion = Cotizacion::factory()->create(['created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.cotizaciones.destroy', [$this->team, $cotizacion]));

    $response->assertRedirect();
    $this->assertSoftDeleted('cotizaciones', ['id' => $cotizacion->id]);
});
