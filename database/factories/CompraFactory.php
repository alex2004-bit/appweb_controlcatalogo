<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => 'C-'.fake()->unique()->numerify('######-######'),
            'proveedor_id' => Proveedor::factory(),
            'created_by' => User::factory(),
            'fecha_compra' => fake()->date(),
            'numero_factura' => null,
            'subtotal' => 0,
            'impuesto' => 0,
            'total' => 0,
            'estado' => 'pendiente',
            'notas' => null,
        ];
    }
}
