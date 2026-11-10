<?php

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

test('web cotización creates cliente and cotizacion', function () {
    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'telefono' => '987654321',
        'documento' => '12345678',
        'tipo_entrega' => 'provincia',
        'direccion' => 'Av. Los Alamos 123',
        'ciudad' => 'El Alto',
        'provincia' => 'La Paz',
        'notas' => 'Entregar por la entrada lateral.',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $response->assertOk()
        ->assertJsonStructure(['codigo', 'total', 'message']);

    $cliente = Cliente::where('email', 'juan@example.com')->first();
    expect($cliente)->not->toBeNull()
        ->and($cliente->documento)->toBe('12345678');

    $cotizacion = Cotizacion::where('cliente_id', $cliente->id)->first();
    expect($cotizacion)->not->toBeNull()
        ->and($cotizacion->estado)->toBe('enviada')
        ->and((float) $cotizacion->total)->toBe(236.00);
    expect($cotizacion->notas)
        ->toContain('Tipo de entrega: Envío a provincia')
        ->toContain('Dirección: Av. Los Alamos 123')
        ->toContain('Ciudad: El Alto')
        ->toContain('Provincia: La Paz')
        ->toContain('Instrucciones: Entregar por la entrada lateral.');

    expect(DetalleCotizacion::where('cotizacion_id', $cotizacion->id)->count())->toBe(1);

    // No se crea Pedido automáticamente - una cotización es solo una solicitud de precio
    expect(Pedido::where('cliente_id', $cliente->id)->exists())->toBeFalse();
});

test('web cotización reuses existing cliente', function () {
    $cliente = Cliente::factory()->create(['email' => 'existente@example.com', 'nombre' => 'Nombre Anterior']);
    $producto = Producto::factory()->create(['precio' => 50.00]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Nombre Anterior',
        'email' => 'existente@example.com',
        'telefono' => '70000001',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertOk();

    $this->assertDatabaseCount('clientes', 1);
    expect(Cotizacion::where('cliente_id', $cliente->id)->exists())->toBeTrue();
});

test('web cotización requires valid items', function () {
    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000002',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => 99999, 'cantidad' => 1],
        ],
    ]);

    $response->assertStatus(422);
});

test('web cotización accepts all delivery options and stores delivery details', function () {
    $deliveryOptions = [
        'domicilio' => [
            'label' => 'Envío a domicilio',
            'details' => [
                'direccion' => 'Calle de prueba 123',
                'ciudad' => 'Cochabamba',
            ],
            'stored' => [
                'Dirección: Calle de prueba 123',
                'Ciudad: Cochabamba',
            ],
            'notStored' => ['Provincia:'],
        ],
        'recoger' => [
            'label' => 'Recoger en tienda',
            'details' => [],
            'stored' => [],
            'notStored' => ['Dirección:', 'Ciudad:', 'Provincia:'],
        ],
        'provincia' => [
            'label' => 'Envío a provincia',
            'details' => [
                'direccion' => 'Calle de prueba 123',
                'ciudad' => 'Cochabamba',
                'provincia' => 'Cochabamba',
            ],
            'stored' => [
                'Dirección: Calle de prueba 123',
                'Ciudad: Cochabamba',
                'Provincia: Cochabamba',
            ],
            'notStored' => [],
        ],
    ];

    foreach ($deliveryOptions as $deliveryOption => $expected) {
        $producto = Producto::factory()->create();

        $this->postJson(route('cotizar'), [
            'nombre' => 'Cliente '.$deliveryOption,
            'telefono' => '70000003',
            'tipo_entrega' => $deliveryOption,
            ...$expected['details'],
            'notas' => 'Llamar al llegar.',
            'items' => [
                ['id' => $producto->id, 'cantidad' => 1],
            ],
        ])->assertSuccessful();

        $cotizacion = Cotizacion::latest('id')->first();

        expect($cotizacion->notas)
            ->toContain('Tipo de entrega: '.$expected['label'])
            ->toContain('Instrucciones: Llamar al llegar.');

        foreach ($expected['stored'] as $storedDetail) {
            expect($cotizacion->notas)->toContain($storedDetail);
        }

        foreach ($expected['notStored'] as $notStoredDetail) {
            expect($cotizacion->notas)->not->toContain($notStoredDetail);
        }
    }
});

test('delivery details are required only for the selected shipping method', function () {
    $producto = Producto::factory()->create();
    $basePayload = [
        'nombre' => 'Cliente sin correo',
        'telefono' => '70000004',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ];

    $this->postJson(route('cotizar'), [
        ...$basePayload,
        'tipo_entrega' => 'domicilio',
        'ciudad' => 'Cochabamba',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('direccion');

    $this->postJson(route('cotizar'), [
        ...$basePayload,
        'tipo_entrega' => 'provincia',
        'direccion' => 'Calle de prueba 123',
        'ciudad' => 'Cochabamba',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('provincia');
});

test('pickup quotes ignore address fields left over from a previous delivery choice', function () {
    $producto = Producto::factory()->create();

    $this->postJson(route('cotizar'), [
        'nombre' => 'Cliente recoge en tienda',
        'telefono' => '70000012',
        'tipo_entrega' => 'recoger',
        'direccion' => 'Dirección anterior',
        'ciudad' => 'Ciudad anterior',
        'provincia' => 'Provincia anterior',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ])->assertSuccessful();

    $cotizacion = Cotizacion::latest('id')->first();

    expect($cotizacion->cliente->direccion)->toBeNull()
        ->and($cotizacion->notas)
        ->not->toContain('Dirección:')
        ->not->toContain('Ciudad:')
        ->not->toContain('Provincia:');
});

test('anonymous customers can request the same products without blocking each other', function () {
    $producto = Producto::factory()->create();
    $payload = [
        'nombre' => 'Cliente sin correo',
        'telefono' => '70000005',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ];

    $this->postJson(route('cotizar'), $payload)->assertSuccessful();
    $this->postJson(route('cotizar'), $payload)->assertSuccessful();

    expect(Cotizacion::count())->toBe(2);
});

test('web cotización prevents an identical recent request from the same email', function () {
    $producto = Producto::factory()->create();
    $payload = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'telefono' => '70000006',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ];

    $this->postJson(route('cotizar'), $payload)->assertSuccessful();
    $this->postJson(route('cotizar'), $payload)->assertTooManyRequests();

    expect(Cotizacion::count())->toBe(1);
});

test('web cotización still rejects invalid delivery options', function () {
    $producto = Producto::factory()->create();

    $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000007',
        'tipo_entrega' => 'mensajeria',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('tipo_entrega');
});

test('web cotización does not decrement product stock', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 5]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000008',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $response->assertOk();

    // El stock NO debe descontarse para una cotización (solo para pedidos confirmados)
    expect($producto->fresh()->stock)->toBe(5);
});

test('web cotización rejects insufficient stock', function () {
    $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 1]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000009',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 3],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['message']);

    expect($producto->fresh()->stock)->toBe(1);
});

test('web cotización uses configured tax rate', function () {
    Configuracion::firstOrCreate([])->update(['impuesto' => 10]);

    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000010',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertOk();

    expect((float) Cotizacion::first()->total)->toBe(110.00);
});

test('web cotización returns the configured payment QR url', function () {
    $qrPath = 'configuracion/qr-pago.png';
    Configuracion::firstOrCreate([])->update(['qr_pago' => $qrPath]);
    $producto = Producto::factory()->create(['precio' => 100.00]);

    $response = $this->postJson(route('cotizar'), [
        'nombre' => 'Juan Pérez',
        'telefono' => '70000011',
        'tipo_entrega' => 'recoger',
        'items' => [
            ['id' => $producto->id, 'cantidad' => 1],
        ],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('qr_pago', Storage::disk('public')->url($qrPath));
});
