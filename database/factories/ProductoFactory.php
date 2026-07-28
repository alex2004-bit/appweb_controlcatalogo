<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nombre' => fake()->unique()->words(3, true),
            'precio' => fake()->randomFloat(2, 10, 1000),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'activo' => true,
        ];
    }
}
