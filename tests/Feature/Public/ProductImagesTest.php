<?php

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

test('public product listings expose URLs for uploaded images', function () {
    Storage::fake('public');
    $categoria = Categoria::factory()->create();
    $imagePath = 'productos/recien-subida.png';
    Storage::disk('public')->put($imagePath, 'image');
    Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'imagen' => $imagePath,
        'activo' => true,
    ]);
    $publicImageUrl = Storage::disk('public')->url($imagePath);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->where('destacados.0.imagen', $publicImageUrl));

    $this->get(route('productos'))
        ->assertInertia(fn ($page) => $page
            ->component('public/Productos')
            ->where('productos.data.0.imagen', $publicImageUrl));
});

test('public product listings normalize legacy storage image paths', function () {
    Storage::fake('public');
    $categoria = Categoria::factory()->create();
    $imagePath = 'productos/legacy.png';
    Storage::disk('public')->put($imagePath, 'image');
    Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'imagen' => '/storage/'.$imagePath,
        'activo' => true,
    ]);

    $this->get(route('productos'))
        ->assertInertia(fn ($page) => $page
            ->where('productos.data.0.imagen', Storage::disk('public')->url($imagePath)));
});

test('selecting a parent category includes products in its subcategories', function () {
    $parent = Categoria::factory()->create(['nombre' => 'Videovigilancia', 'parent_id' => null]);
    $child = Categoria::factory()->create(['nombre' => 'Cámaras IP', 'parent_id' => $parent->id]);

    Producto::factory()->create([
        'categoria_id' => $parent->id,
        'nombre' => 'Grabador sin subcategoría',
        'activo' => true,
    ]);
    Producto::factory()->create([
        'categoria_id' => $child->id,
        'nombre' => 'Cámara IP',
        'activo' => true,
    ]);

    $this->get(route('productos', ['categoria' => $parent->id]))
        ->assertInertia(fn ($page) => $page
            ->where('productos.total', 2)
            ->where('categorias.0.productos_count', 2));
});
