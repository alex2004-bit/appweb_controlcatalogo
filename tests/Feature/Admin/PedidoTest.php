<?php

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;

    $this->user->assignRole('Super Admin');
});

test('guests cannot access pedidos', function () {
    $response = $this->get(route('admin.pedidos.index', $this->team));

    $response->assertRedirect(route('login'));
});

test('authenticated users can list pedidos', function () {
    Pedido::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.pedidos.index', $this->team));

    $response->assertOk();
});

test('pedido can be created with items', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 150.00]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 2,
                    'precio_unitario' => 150.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
    ]);

    $this->assertDatabaseHas('detalle_pedido', [
        'item_nombre' => $producto->nombre,
        'cantidad' => 2,
    ]);
});

test('pedido can be created and marked as paid', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'efectivo',
            'pagado' => true,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 1,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
        'pagado' => true,
        'metodo_pago' => 'efectivo',
        'estado' => 'completado',
    ]);
});

test('pedido requires at least one item', function () {
    $cliente = Cliente::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [],
        ]);

    $response->assertSessionHasErrors('items');
});

test('pedido can be updated', function () {
    $cliente = Cliente::factory()->create();
    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.pedidos.update', [$this->team, $pedido]), [
            'cliente_id' => $cliente->id,
            'estado' => 'completado',
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => 0,
                    'item_nombre' => 'Item de prueba',
                    'cantidad' => 1,
                    'precio_unitario' => 50,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertEquals('completado', $pedido->fresh()->estado);
});

test('pedido can be deleted', function () {
    $cliente = Cliente::factory()->create();
    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.pedidos.destroy', [$this->team, $pedido]));

    $response->assertRedirect();

    $this->assertSoftDeleted('pedidos', [
        'id' => $pedido->id,
    ]);
});
