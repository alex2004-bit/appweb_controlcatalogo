<?php

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = $this->user->currentTeam;
    $this->user->assignRole('Super Admin');
});

test('guests cannot access generales config', function () {
    $response = $this->get(route('admin.configuracion.generales.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view generales config', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.configuracion.generales.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/configuracion/Generales'));
});

test('generales config can be updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.generales.update', $this->team), [
            'moneda' => 'USD',
            'impuesto' => 18,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('configuraciones', ['moneda' => 'USD', 'impuesto' => 18]);
});

test('generales config stores payment qr image', function () {
    Storage::fake('public');

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.generales.update', $this->team), [
            'moneda' => 'PEN',
            'impuesto' => 18,
            'qr_pago' => UploadedFile::fake()->image('qr.png'),
        ]);

    $response->assertRedirect();

    $config = Configuracion::firstOrCreate([]);
    expect($config->qr_pago)->not->toBeNull();

    Storage::disk('public')->assertExists($config->qr_pago);
});

test('generales config stores homepage banner images', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->put(route('admin.configuracion.generales.update', $this->team), [
            'moneda' => 'Bs',
            'impuesto' => 13,
            'banner_imagen_1' => UploadedFile::fake()->image('banner-1.jpg', 1200, 500),
            'banner_imagen_2' => UploadedFile::fake()->image('banner-2.jpg', 1200, 500),
            'banner_imagen_3' => UploadedFile::fake()->image('banner-3.jpg', 1200, 500),
        ])
        ->assertRedirect();

    $config = Configuracion::firstOrFail();

    expect($config->banner_imagen_1)->not->toBeNull()
        ->and($config->banner_imagen_2)->not->toBeNull()
        ->and($config->banner_imagen_3)->not->toBeNull();

    Storage::disk('public')->assertExists([
        $config->banner_imagen_1,
        $config->banner_imagen_2,
        $config->banner_imagen_3,
    ]);
});

test('home page uses configured banner images', function () {
    Storage::fake('public');
    $bannerPath = UploadedFile::fake()->image('banner.jpg', 1200, 500)
        ->store('configuracion/banners', 'public');
    Configuracion::firstOrCreate([])->update(['banner_imagen_1' => $bannerPath]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->where('bannerImages.0', Storage::disk('public')->url($bannerPath))
            ->where('bannerImages.1', null)
            ->where('bannerImages.2', null));
});

test('guests cannot access social config', function () {
    $response = $this->get(route('admin.configuracion.social.index', $this->team));
    $response->assertRedirect(route('login'));
});

test('social config can be viewed and updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.configuracion.social.index', $this->team));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/configuracion/Social'));

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.configuracion.social.update', $this->team), [
            'whatsapp' => '+51999888777',
            'facebook' => 'https://facebook.com/miempresa',
            'instagram' => '@miempresa',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('configuraciones', ['whatsapp' => '+51999888777']);
});
