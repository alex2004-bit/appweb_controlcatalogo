<?php

use App\Models\Galeria;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');

    Storage::fake('public');
});

test('guests cannot access galeria', function () {
    $response = $this->get(route('admin.catalogo.galeria.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can list galeria', function () {
    Galeria::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.catalogo.galeria.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/catalogo/galeria/Index')
        ->has('galeria.data', 3)
    );
});

test('galeria item can be created with image', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.galeria.store', $this->team), [
            'titulo' => 'Test Image',
            'descripcion' => 'Test description',
            'imagen' => $file,
            'tipo' => 'imagen',
            'activo' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('galeria', [
        'titulo' => 'Test Image',
        'descripcion' => 'Test description',
        'tipo' => 'imagen',
        'activo' => true,
    ]);

    $galeria = Galeria::where('titulo', 'Test Image')->first();
    expect($galeria->imagen)->not->toBeNull();
    Storage::disk('public')->assertExists($galeria->imagen);
});

test('galeria item creation validates image mime type', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.galeria.store', $this->team), [
            'titulo' => 'Test PDF',
            'imagen' => $file,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('imagen');
});

test('galeria item creation validates image max size', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(6000); // 6MB

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.catalogo.galeria.store', $this->team), [
            'titulo' => 'Large Image',
            'imagen' => $file,
            'activo' => true,
        ]);

    $response->assertSessionHasErrors('imagen');
});

test('galeria item can be updated with new image', function () {
    $oldFile = UploadedFile::fake()->image('old.jpg', 800, 600);
    $item = Galeria::factory()->create(['titulo' => 'Original', 'activo' => true, 'imagen' => $oldFile->store('galeria', 'public')]);
    $oldPath = $item->imagen;

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new.jpg', 800, 600);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.galeria.update', [$this->team, $item]), [
            'titulo' => 'Updated',
            'imagen' => $newFile,
            'activo' => true,
        ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->imagen)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($item->imagen);
});

test('galeria item update without new image keeps old image', function () {
    $oldFile = UploadedFile::fake()->image('old.jpg', 800, 600);
    $item = Galeria::factory()->create(['titulo' => 'Original', 'activo' => true, 'imagen' => $oldFile->store('galeria', 'public')]);
    $oldPath = $item->imagen;

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.catalogo.galeria.update', [$this->team, $item]), [
            'titulo' => 'Updated without image',
            'activo' => true,
        ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->imagen)->toBe($oldPath);
    Storage::disk('public')->assertExists($oldPath);
});

test('galeria item can be deleted', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600);
    $item = Galeria::factory()->create(['imagen' => $file->store('galeria', 'public')]);
    $imagePath = $item->imagen;

    Storage::disk('public')->assertExists($imagePath);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.galeria.destroy', [$this->team, $item]));

    $response->assertRedirect();
    $this->assertSoftDeleted('galeria', ['id' => $item->id]);
    Storage::disk('public')->assertMissing($imagePath);
});

test('galeria item deletion with missing file does not error', function () {
    $item = Galeria::factory()->create(['imagen' => 'galeria/missing.jpg']);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.catalogo.galeria.destroy', [$this->team, $item]));

    $response->assertRedirect();
    $this->assertSoftDeleted('galeria', ['id' => $item->id]);
});
