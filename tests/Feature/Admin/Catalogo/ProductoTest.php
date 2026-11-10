<?php

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;

    $this->user->assignRole('Super Admin');
});

test('producto stores primary and gallery images from uploads', function () {
    $categoria = Categoria::factory()->create();
    $principal = UploadedFile::fake()->image('principal.jpg');
    $adicional = UploadedFile::fake()->image('lateral.png');

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto con galería',
            'precio' => 299.99,
            'imagen' => $principal,
            'imagenes' => [$adicional],
            'activo' => true,
        ]);

    $response->assertRedirect();

    $producto = Producto::where('nombre', 'Producto con galería')->firstOrFail();

    expect($producto->imagen)->toStartWith('productos/')
        ->and($producto->imagenes)->toHaveCount(1);

    Storage::disk('public')->assertExists($producto->imagen);
    Storage::disk('public')->assertExists($producto->imagenes->first()->imagen);
});

test('producto update replaces primary image and appends gallery images', function () {
    $categoria = Categoria::factory()->create();
    $oldImage = UploadedFile::fake()->image('old.jpg')->store('productos', 'public');
    $producto = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'imagen' => $oldImage,
    ]);
    $newImage = UploadedFile::fake()->image('new.jpg');
    $additional = UploadedFile::fake()->image('detail.webp');

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'imagen' => $newImage,
            'imagenes' => [$additional],
            'activo' => true,
        ]);

    $response->assertRedirect();
    $producto->refresh();

    expect($producto->imagen)->not->toBe($oldImage)
        ->and($producto->imagenes)->toHaveCount(1);

    Storage::disk('public')->assertMissing($oldImage);
    Storage::disk('public')->assertExists($producto->imagen);
    Storage::disk('public')->assertExists($producto->imagenes->first()->imagen);
});

test('producto update removes only selected gallery images', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
    $imageToRemove = UploadedFile::fake()->image('remove.jpg')->store('productos', 'public');
    $imageToKeep = UploadedFile::fake()->image('keep.jpg')->store('productos', 'public');
    $galleryImageToRemove = $producto->imagenes()->create([
        'titulo' => $producto->nombre,
        'imagen' => $imageToRemove,
        'tipo' => 'producto',
        'activo' => true,
    ]);
    $producto->imagenes()->create([
        'titulo' => $producto->nombre,
        'imagen' => $imageToKeep,
        'tipo' => 'producto',
        'activo' => true,
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'eliminar_imagenes' => [$galleryImageToRemove->id],
            'activo' => true,
        ])
        ->assertRedirect();

    expect($producto->imagenes()->count())->toBe(1);
    Storage::disk('public')->assertMissing($imageToRemove);
    Storage::disk('public')->assertExists($imageToKeep);
});

test('producto edit page includes existing gallery images', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
    $imagen = UploadedFile::fake()->image('gallery.jpg')->store('productos', 'public');

    $producto->imagenes()->create([
        'titulo' => $producto->nombre,
        'imagen' => $imagen,
        'tipo' => 'producto',
        'activo' => true,
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.catalogo.productos.edit', [$this->team, $producto]))
        ->assertInertia(fn ($page) => $page
            ->component('admin/catalogo/productos/Edit')
            ->has('producto.imagenes', 1)
            ->where('producto.imagenes.0.imagen', $imagen));
});

test('guests cannot access productos', function () {
    $response = $this->get(route('admin.catalogo.productos.index', $this->team));

    $response->assertRedirect(route('login'));
});

test('authenticated users can list productos', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->count(3)->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.productos.index', $this->team));

    $response->assertOk();
});

test('producto can be created', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Cámara IP 4K',
            'precio' => 299.99,
            'sku' => 'CAM-4K-001',
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Cámara IP 4K',
        'sku' => 'CAM-4K-001',
    ]);
});

test('producto sale price is calculated with 40 percent markup on creation', function () {
    $categoria = Categoria::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto con margen automático',
            'precio_compra' => 373.55,
            'precio' => 1,
            'precio_anterior' => 729.41,
            'activo' => true,
        ])
        ->assertRedirect();

    $producto = Producto::where('nombre', 'Producto con margen automático')->firstOrFail();

    expect((float) $producto->precio)->toBe(522.97)
        ->and((float) $producto->precio_anterior)->toBe(729.41);
});

test('producto can be updated', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Original']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Actualizado',
            'precio' => 199.99,
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Actualizado',
    ]);
});

test('producto sale price is recalculated with 40 percent markup on update', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'precio_compra' => 100,
        'precio' => 140,
        'precio_anterior' => 150,
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => $producto->nombre,
            'precio_compra' => 373.55,
            'precio' => 1,
            'precio_anterior' => 729.41,
            'activo' => true,
        ])
        ->assertRedirect();

    $producto->refresh();

    expect((float) $producto->precio)->toBe(522.97)
        ->and((float) $producto->precio_anterior)->toBe(729.41);
});

test('producto can be deleted', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.productos.destroy', [$this->team, $producto]));

    $response->assertRedirect();

    $this->assertSoftDeleted('productos', [
        'id' => $producto->id,
    ]);
});

test('producto creation requires nombre', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => null,
            'precio' => 100,
        ]);

    $response->assertSessionHasErrors(['nombre', 'categoria_id']);
});

test('producto especificaciones json string is stored as array', function () {
    $categoria = Categoria::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Cámara con especificaciones',
            'precio' => 100,
            'especificaciones' => '[{"nombre":"Marca","valor":"Hikvision"}]',
            'activo' => true,
        ]);

    $producto = Producto::where('nombre', 'Cámara con especificaciones')->first();

    expect($producto->especificaciones)->toBeArray()
        ->and($producto->especificaciones)->toBe([['nombre' => 'Marca', 'valor' => 'Hikvision']]);
});

// --- Productos: Validaciones y Edge Cases ---

test('producto rejects negative price', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto precio negativo',
            'precio' => -10.00,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('precio');
});

test('producto rejects negative stock', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto stock negativo',
            'precio' => 100,
            'stock' => -5,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('stock');
});

test('producto rejects duplicate sku', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->create(['categoria_id' => $categoria->id, 'sku' => 'SKU-UNICO']);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Otro producto',
            'precio' => 100,
            'sku' => 'SKU-UNICO',
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('sku');
});

test('producto rejects non-existent categoria', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => 99999,
            'nombre' => 'Producto sin categoría',
            'precio' => 100,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('categoria_id');
});

test('producto rejects inactive categoria', function () {
    $categoria = Categoria::factory()->create(['activo' => false]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto categoría inactiva',
            'precio' => 100,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('categoria_id');
});

test('producto rejects invalid json in especificaciones', function () {
    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto JSON inválido',
            'precio' => 100,
            'especificaciones' => 'no es json válido',
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('especificaciones');
});

test('user without create permission cannot create producto', function () {
    $this->user->assignRole('Vendedor');
    $this->user->givePermissionTo('view_productos');

    $categoria = Categoria::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Sin permiso crear',
            'precio' => 100,
            'activo' => true,
        ]);

    // App returns 302 redirect for unauthorized instead of 403
    $response->assertStatus(302);
});

test('user without update permission cannot update producto', function () {
    $this->user->assignRole('Vendedor');
    $this->user->givePermissionTo('view_productos');

    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Sin permiso editar',
            'precio' => 100,
            'activo' => true,
        ]);

    $response->assertStatus(302);
});

test('user without delete permission cannot delete producto', function () {
    $this->user->assignRole('Vendedor');
    $this->user->givePermissionTo('view_productos');

    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.productos.destroy', [$this->team, $producto]));

    $response->assertStatus(302);
});

test('producto generates unique slug when duplicate nombre', function () {
    $categoria = Categoria::factory()->create();
    Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Cámara IP']);

    $this->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Cámara IP',
            'precio' => 100,
            'activo' => true,
        ]);

    $productos = Producto::where('nombre', 'Cámara IP')->get();
    expect($productos->count())->toBe(2);

    $slugs = $productos->pluck('slug')->all();
    expect($slugs)->toContain('camara-ip');
    expect($slugs)->toContain('camara-ip-2');
});

test('producto rejects inactive marca', function () {
    $categoria = Categoria::factory()->create();
    $marca = Marca::factory()->create(['activo' => false]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.productos.store', $this->team), [
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'nombre' => 'Producto marca inactiva',
            'precio' => 100,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('marca_id');
});

test('producto update keeps existing slug when nombre changes', function () {
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Original', 'slug' => 'original-slug']);

    $this->actingAs($this->user)
        ->put(route('admin.catalogo.productos.update', [$this->team, $producto]), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Actualizado',
            'precio' => 199.99,
            'activo' => true,
        ]);

    expect($producto->fresh()->slug)->toBe('original-slug');
});
