<?php

use App\Models\Consulta;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access consultas', function () {
    $response = $this->get(route('admin.consultas.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list consultas', function () {
    Consulta::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.consultas.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/consultas/Index')
        ->has('consultas.data', 3)
    );
});

test('consulta can be shown and marked as read', function () {
    $consulta = Consulta::factory()->create(['leido' => false]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.consultas.show', [$this->team, $consulta]));

    $response->assertOk();
    $this->assertTrue($consulta->fresh()->leido);
    $this->assertEquals($this->user->id, $consulta->fresh()->leido_by);
});

test('consulta can be updated', function () {
    $consulta = Consulta::factory()->create(['estado' => 'pendiente']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.consultas.update', [$this->team, $consulta]), [
            'estado' => 'respondido',
        ]);

    $response->assertRedirect();
    $this->assertEquals('respondido', $consulta->fresh()->estado);
});

test('consulta can be deleted', function () {
    $consulta = Consulta::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.consultas.destroy', [$this->team, $consulta]));

    $response->assertRedirect();
    $this->assertModelMissing($consulta);
});
