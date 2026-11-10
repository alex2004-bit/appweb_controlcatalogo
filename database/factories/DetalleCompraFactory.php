<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleCompra>
 */
class DetalleCompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'compra_id' => Compra::factory(),
            'producto_id' => Producto::factory(),
            'producto_nombre' => fake()->words(3, true),
            'cantidad' => fake()->numberBetween(1, 20),
            'precio_unitario' => fake()->randomFloat(2, 1, 500),
            'subtotal' => fake()->randomFloat(2, 1, 10000),
        ];
    }
}
