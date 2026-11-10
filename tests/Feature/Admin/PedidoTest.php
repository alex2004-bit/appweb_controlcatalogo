<?php

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\DetallePedido;
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

test('pedido stores additional sale data', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 200.00]);
    $vendedor = User::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'referido_por' => 'Juan López',
            'metodo_pago' => 'transferencia',
            'direccion_entrega' => 'Av. Los Olivos 123, Lima',
            'fecha_entrega' => '2026-08-25 15:00:00',
            'telefono_contacto' => '987654321',
            'anticipo' => 100.00,
            'numero_cuotas' => 3,
            'plazo_dias' => 30,
            'notas' => 'Entregar en puerta',
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 1,
                    'precio_unitario' => 200.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'referido_por' => 'Juan López',
        'direccion_entrega' => 'Av. Los Olivos 123, Lima',
        'telefono_contacto' => '987654321',
        'anticipo' => '100.00',
        'numero_cuotas' => 3,
        'plazo_dias' => 30,
        'metodo_pago' => 'transferencia',
    ]);

    $pedido = Pedido::where('cliente_id', $cliente->id)->first();

    $this->assertEquals(136.00, $pedido->saldo_pendiente);
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
    $producto = Producto::factory()->create(['precio' => 50.00]);
    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.pedidos.update', [$this->team, $pedido]), [
            'cliente_id' => $cliente->id,
            'estado' => 'completado',
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
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

test('pedido decrements product stock on creation', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 3,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    expect($producto->fresh()->stock)->toBe(7);
});

test('pedido rejects insufficient stock', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 2]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 5,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertSessionHasErrors('items');

    expect($producto->fresh()->stock)->toBe(2);
});

test('pedido cancel restores product stock', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 8]);

    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);
    DetallePedido::create([
        'pedido_id' => $pedido->id,
        'item_type' => Producto::class,
        'item_id' => $producto->id,
        'item_nombre' => $producto->nombre,
        'cantidad' => 2,
        'precio_unitario' => 100.00,
        'descuento' => 0,
        'subtotal' => 200.00,
    ]);
    $producto->decrement('stock', 2);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.pedidos.update', [$this->team, $pedido]), [
            'cliente_id' => $cliente->id,
            'estado' => 'cancelado',
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

    expect($producto->fresh()->stock)->toBe(8);
});

test('reactivating a cancelled pedido deducts only its new reserved stock', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
    $pedido = Pedido::factory()->create([
        'cliente_id' => $cliente->id,
        'created_by' => $this->user->id,
        'estado' => 'cancelado',
    ]);

    DetallePedido::create([
        'pedido_id' => $pedido->id,
        'item_type' => Producto::class,
        'item_id' => $producto->id,
        'item_nombre' => $producto->nombre,
        'cantidad' => 2,
        'precio_unitario' => 100.00,
        'descuento' => 0,
        'subtotal' => 200.00,
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.pedidos.update', [$this->team, $pedido]), [
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
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
        ])
        ->assertRedirect();

    expect($producto->fresh()->stock)->toBe(8);
});

test('deleting a cancelled pedido does not restore stock a second time', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
    $pedido = Pedido::factory()->create([
        'cliente_id' => $cliente->id,
        'created_by' => $this->user->id,
        'estado' => 'cancelado',
    ]);

    DetallePedido::create([
        'pedido_id' => $pedido->id,
        'item_type' => Producto::class,
        'item_id' => $producto->id,
        'item_nombre' => $producto->nombre,
        'cantidad' => 2,
        'precio_unitario' => 100.00,
        'descuento' => 0,
        'subtotal' => 200.00,
    ]);

    $this->actingAs($this->user)
        ->delete(route('admin.pedidos.destroy', [$this->team, $pedido]))
        ->assertRedirect();

    expect($producto->fresh()->stock)->toBe(10);
});

test('pedido delete restores product stock', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 6]);

    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);
    DetallePedido::create([
        'pedido_id' => $pedido->id,
        'item_type' => Producto::class,
        'item_id' => $producto->id,
        'item_nombre' => $producto->nombre,
        'cantidad' => 2,
        'precio_unitario' => 100.00,
        'descuento' => 0,
        'subtotal' => 200.00,
    ]);
    $producto->decrement('stock', 2);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.pedidos.destroy', [$this->team, $pedido]));

    $response->assertRedirect();

    expect($producto->fresh()->stock)->toBe(6);
});

test('pedido uses configured tax rate', function () {
    Configuracion::firstOrCreate([])->update(['impuesto' => 10]);

    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
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

    expect((float) Pedido::first()->total)->toBe(110.00);
});

// --- Pedidos: Validaciones y Edge Cases ---

test('pedido rejects non-existent item_id', function () {
    $cliente = Cliente::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => 99999,
                    'item_nombre' => 'Inexistente',
                    'cantidad' => 1,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertSessionHasErrors('items');
});

test('pedido rejects inactive producto', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => false]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
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

    $response->assertSessionHasErrors('items');
});

test('pedido aggregates same product in multiple lines', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 3,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 4,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    expect($producto->fresh()->stock)->toBe(3); // 10 - 3 - 4 = 3;
});

test('pedido edit adjusts stock correctly', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

    $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id, 'created_by' => $this->user->id]);
    DetallePedido::create([
        'pedido_id' => $pedido->id,
        'item_type' => Producto::class,
        'item_id' => $producto->id,
        'item_nombre' => $producto->nombre,
        'cantidad' => 3,
        'precio_unitario' => 100.00,
        'descuento' => 0,
        'subtotal' => 300.00,
    ]);
    $producto->decrement('stock', 3);

    // Cambiar cantidad de 3 a 5
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.pedidos.update', [$this->team, $pedido]), [
            'cliente_id' => $cliente->id,
            'estado' => 'pendiente',
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 5,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    // Stock debería ser 10 - 5 = 5 (se devolvieron los 3 y se quitaron 5)
    expect($producto->fresh()->stock)->toBe(5);
});

test('pedido create rolls back on detail error', function () {
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

    // Simular error forzando un item_id que no existe
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.pedidos.store', $this->team), [
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'item_type' => 'producto',
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => 1,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
                [
                    'item_type' => 'producto',
                    'item_id' => 99999, // Inexistente
                    'item_nombre' => 'Inexistente',
                    'cantidad' => 1,
                    'precio_unitario' => 100.00,
                    'descuento' => 0,
                ],
            ],
        ]);

    $response->assertSessionHasErrors('items');

    // No debe haber pedido creado (rollback)
    $this->assertDatabaseCount('pedidos', 0);
    $this->assertDatabaseCount('detalle_pedido', 0);

    // Stock no debe haber cambiado
    expect($producto->fresh()->stock)->toBe(10);
});
