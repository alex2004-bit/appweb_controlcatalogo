<?php

namespace Database\Factories;

use App\Models\Galeria;
use Illuminate\Database\Eloquent\Factories\Factory;

class GaleriaFactory extends Factory
{
    protected $model = Galeria::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->sentence(),
            'imagen' => 'galeria/'.fake()->uuid().'.jpg',
            'tipo' => fake()->randomElement(['imagen', 'video']),
            'activo' => true,
        ];
    }
}
