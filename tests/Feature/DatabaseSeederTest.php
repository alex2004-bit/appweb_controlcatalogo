<?php

use App\Models\Categoria;
use Database\Seeders\DatabaseSeeder;

test('demo catalog categories use stable slugs and a two level hierarchy', function () {
    $this->seed(DatabaseSeeder::class);

    $videovigilancia = Categoria::query()->where('slug', 'videovigilancia')->firstOrFail();
    $alarmas = Categoria::query()->where('slug', 'alarmas')->firstOrFail();
    $controlAcceso = Categoria::query()->where('slug', 'control-de-acceso')->firstOrFail();

    expect($videovigilancia->children()->pluck('slug')->all())
        ->toEqualCanonicalizing([
            'camaras-ip',
            'camaras-analogicas',
            'camaras-termicas',
            'otros-grabadores',
            'nvr-ip',
            'dvr-hd',
        ])
        ->and($alarmas->children()->pluck('slug')->all())
        ->toEqualCanonicalizing(['sensores'])
        ->and($controlAcceso->children()->pluck('slug')->all())
        ->toEqualCanonicalizing(['cerraduras-electricas']);
});
