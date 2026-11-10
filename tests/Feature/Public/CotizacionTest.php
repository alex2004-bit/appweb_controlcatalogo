<?php

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\Producto;

beforeEach(function () {
    Configuracion::firstOrCreate([]);
});

test('public cotizacion rejects duplicate submission (same email + items within 5 min)', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);

    $payload = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ];

    // Primera solicitud
    $response1 = $this->postJson(route('cotizar'), $payload);
    $response1->assertStatus(200);

    // Segunda solicitud inmediata (duplicado)
    $response2 = $this->postJson(route('cotizar'), $payload);
    $response2->assertStatus(429);
    $response2->assertJson(['message' => 'Ya se envió una cotización similar recientemente. Espere unos minutos.']);
});

test('public cotizacion rejects excessive quantity (max 200 units total)', function () {
    $producto1 = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 500]);
    $producto2 = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 500]);
    $producto3 = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 500]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto1->id, 'cantidad' => 70],
            ['id' => $producto2->id, 'cantidad' => 70],
            ['id' => $producto3->id, 'cantidad' => 70],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'El total de unidades solicitadas excede el límite permitido (200).']);
});

test('public cotizacion rejects deleted producto', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);
    $producto->delete(); // Soft delete

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'No se encontraron productos válidos.']);
});

test('public cotizacion rejects inactive producto', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => false, 'stock' => 10]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'No se encontraron productos válidos.']);
});

test('public cotizacion rejects excess stock', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 5]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 10],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => "Stock insuficiente para {$producto->nombre}. Disponible: 5, solicitado: 10."]);
});

test('public cotizacion rejects too many items (max 20)', function () {
    $productos = Producto::factory()->count(21)->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);

    $items = [];
    foreach ($productos as $p) {
        $items[] = ['id' => $p->id, 'cantidad' => 1];
    }

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => $items,
    ]);

    $response->assertStatus(422);
    $response->assertJsonStructure(['message', 'errors']);
});

test('public cotizacion respects rate limiting per IP', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 100]);

    // Hacer 5 solicitudes rápidas
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson(route('cotizar'), [
            'nombre' => "Usuario {$i}",
            'email' => "user{$i}@test.com",
            'telefono' => '987654321',
            'tipo_entrega' => 'recoger',
            'items' => [
                ['id' => $producto->id, 'cantidad' => 1],
            ],
        ]);
        $response->assertStatus(200);
    }

    // La 6ta debe ser rate limited
    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Usuario 6',
        'email' => 'user6@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(429);
    $response->assertJson(['message' => 'Demasiadas solicitudes. Intente nuevamente en un minuto.']);
});

test('public cotizacion creates cotizacion with correct code format', function () {
    $producto = Producto::factory()->create(['precio' => 150.00, 'activo' => true, 'stock' => 10]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    expect($data['codigo'])->toMatch('/^COT-\d{8}-\d{4}$/');
    expect($data['total'])->toBeGreaterThan(0);
});

test('public cotizacion aggregates same product in multiple lines', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 3],
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    // Debe agregar las cantidades: 3 + 2 = 5
    $cotizacion = Cotizacion::where('codigo', $data['codigo'])->first();
    $detalle = $cotizacion->detalles->first();

    expect($detalle->cantidad)->toBe(5);
});

test('public cotizacion does not decrement stock', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);
    $stockInicial = $producto->stock;

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'telefono' => '987654321',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 3],
        ],
    ]);

    $response->assertStatus(200);

    // Stock NO debe decrementar en cotizaciones
    expect($producto->fresh()->stock)->toBe($stockInicial);
});

test('public cotizacion creates cliente if not exists', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Nuevo Cliente',
        'email' => 'nuevo@test.com',
        'telefono' => '987654321',
        'documento' => '12345678',
        'tipo_entrega' => 'domicilio',
        'direccion' => 'Av. Principal 123',
        'ciudad' => 'Cochabamba',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('clientes', [
        'email' => 'nuevo@test.com',
        'nombre' => 'Nuevo Cliente',
        'telefono' => '987654321',
        'documento' => '12345678',
        'direccion' => 'Av. Principal 123',
    ]);
});

test('public cotizacion updates existing cliente info', function () {
    $cliente = Cliente::factory()->create(['email' => 'existente@test.com', 'telefono' => '111111111']);
    $producto = Producto::factory()->create(['precio' => 100.00, 'activo' => true, 'stock' => 10]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Cliente Actualizado',
        'email' => 'existente@test.com',
        'telefono' => '999999999',
        'tipo_entrega' => 'domicilio',
        'direccion' => 'Nueva Dirección',
        'ciudad' => 'Cochabamba',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(200);

    expect($cliente->fresh()->telefono)->toBe('999999999');
    expect($cliente->fresh()->direccion)->toBe('Nueva Dirección');
});
