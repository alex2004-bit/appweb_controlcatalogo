<?php

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('catalog entities store uploaded images in their dedicated folders', function () {
    $categoryResponse = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.categorias.store', $this->team), [
            'nombre' => 'Cámaras',
            'imagen' => UploadedFile::fake()->image('categoria.jpg'),
            'activo' => true,
        ]);

    $categoryResponse->assertRedirect();
    expect(Categoria::latest('id')->value('imagen'))->toStartWith('catalogo/categorias/');

    $brandResponse = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.marcas.store', $this->team), [
            'nombre' => 'Dahua',
            'logo' => UploadedFile::fake()->image('marca.jpg'),
            'activo' => true,
        ]);

    $brandResponse->assertRedirect();
    expect(Marca::latest('id')->value('logo'))->toStartWith('catalogo/marcas/');

    $serviceResponse = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.servicios.store', $this->team), [
            'categoria_id' => Categoria::latest('id')->value('id'),
            'nombre' => 'Instalación',
            'imagen' => UploadedFile::fake()->image('servicio.jpg'),
            'activo' => true,
        ]);

    $serviceResponse->assertRedirect();
    expect(Servicio::latest('id')->value('imagen'))->toStartWith('catalogo/servicios/');
});
