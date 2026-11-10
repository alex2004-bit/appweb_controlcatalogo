<?php

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('pending purchases do not change stock until they are received', function () {
    $proveedor = Proveedor::factory()->create();
    $producto = Producto::factory()->create([
        'stock' => 4,
        'precio_compra' => 10,
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.compras.store', $this->team), [
            'proveedor_id' => $proveedor->id,
            'fecha_compra' => '2026-10-06',
            'numero_factura' => 'F-100',
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_unitario' => 12.5],
            ],
        ])
        ->assertRedirect();

    $compra = Compra::query()->with('detalles')->firstOrFail();

    expect($compra->estado)->toBe('pendiente')
        ->and($compra->detalles)->toHaveCount(1)
        ->and((int) $producto->refresh()->stock)->toBe(4);
});

test('users without purchase permissions are forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.abastecimiento.compras.index', $user->currentTeam))
        ->assertForbidden();
});

test('receiving a purchase adds stock and saves the latest purchase cost exactly once', function () {
    $proveedor = Proveedor::factory()->create();
    $producto = Producto::factory()->create([
        'stock' => 4,
        'precio_compra' => 10,
    ]);
    $compra = createPurchase($proveedor, $producto, $this->user);

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.compras.receive', [$this->team, $compra]))
        ->assertRedirect(route('admin.abastecimiento.compras.show', [$this->team, $compra]));

    expect((int) $producto->refresh()->stock)->toBe(7)
        ->and((float) $producto->precio_compra)->toBe(12.5)
        ->and($compra->refresh()->estado)->toBe('recibida')
        ->and($compra->recibida_at)->not->toBeNull();

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.compras.receive', [$this->team, $compra]))
        ->assertSessionHasErrors('compra');

    expect((int) $producto->refresh()->stock)->toBe(7);
});

test('canceling a pending purchase leaves inventory unchanged', function () {
    $proveedor = Proveedor::factory()->create();
    $producto = Producto::factory()->create(['stock' => 2]);
    $compra = createPurchase($proveedor, $producto, $this->user);

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.compras.cancel', [$this->team, $compra]))
        ->assertRedirect(route('admin.abastecimiento.compras.show', [$this->team, $compra]));

    expect($compra->refresh()->estado)->toBe('cancelada')
        ->and((int) $producto->refresh()->stock)->toBe(2);
});

test('invalid and duplicate purchase products are rejected', function () {
    $proveedor = Proveedor::factory()->create();
    $producto = Producto::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.abastecimiento.compras.store', $this->team), [
            'proveedor_id' => $proveedor->id,
            'fecha_compra' => '2026-10-06',
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 5],
                ['producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 5],
            ],
        ])
        ->assertSessionHasErrors('items.1.producto_id');

    expect(Compra::query()->count())->toBe(0);
});

function createPurchase(Proveedor $proveedor, Producto $producto, User $user): Compra
{
    $compra = Compra::factory()->for($proveedor, 'proveedor')->create([
        'created_by' => $user->id,
        'subtotal' => 37.5,
        'impuesto' => 0,
        'total' => 37.5,
    ]);
    $compra->detalles()->create([
        'producto_id' => $producto->id,
        'producto_nombre' => $producto->nombre,
        'cantidad' => 3,
        'precio_unitario' => 12.5,
        'subtotal' => 37.5,
    ]);

    return $compra;
}
