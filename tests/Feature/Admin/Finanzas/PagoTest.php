<?php

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Pago;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access pagos', function () {
    $response = $this->get(route('admin.finanzas.pagos.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list pagos', function () {
    $cotizacion = Cotizacion::factory()->create(['created_by' => $this->user->id]);
    Pago::factory()->count(3)->create(['cotizacion_id' => $cotizacion->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.finanzas.pagos.index', $this->team));

    $response->assertOk();
});

test('pago can be created', function () {
    $cliente = Cliente::factory()->create();
    $cotizacion = Cotizacion::factory()->create([
        'cliente_id' => $cliente->id,
        'created_by' => $this->user->id,
        'total' => 500.00,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.finanzas.pagos.store', $this->team), [
            'cotizacion_id' => $cotizacion->id,
            'monto' => 200.00,
            'metodo_pago' => 'transferencia',
            'fecha_pago' => now()->format('Y-m-d'),
            'estado' => 'completado',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('pagos', [
        'cotizacion_id' => $cotizacion->id,
        'monto' => 200.00,
    ]);
});

test('pago amount cannot exceed saldo pendiente', function () {
    $cliente = Cliente::factory()->create();
    $cotizacion = Cotizacion::factory()->create([
        'cliente_id' => $cliente->id,
        'created_by' => $this->user->id,
        'total' => 100.00,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.finanzas.pagos.store', $this->team), [
            'cotizacion_id' => $cotizacion->id,
            'monto' => 999.00,
            'metodo_pago' => 'efectivo',
            'fecha_pago' => now()->format('Y-m-d'),
            'estado' => 'completado',
        ]);

    $response->assertSessionHasErrors('monto');
});

test('pago can be shown', function () {
    $cotizacion = Cotizacion::factory()->create(['created_by' => $this->user->id]);
    $pago = Pago::factory()->create(['cotizacion_id' => $cotizacion->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.finanzas.pagos.show', [$this->team, $pago]));

    $response->assertOk();
});

test('pago can be deleted', function () {
    $cotizacion = Cotizacion::factory()->create(['created_by' => $this->user->id]);
    $pago = Pago::factory()->create(['cotizacion_id' => $cotizacion->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.finanzas.pagos.destroy', [$this->team, $pago]));

    $response->assertRedirect();
    $this->assertSoftDeleted('pagos', ['id' => $pago->id]);
});
