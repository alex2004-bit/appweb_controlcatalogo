<?php

namespace Database\Factories;

use App\Models\Consulta;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultaFactory extends Factory
{
    protected $model = Consulta::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'email' => fake()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'mensaje' => fake()->paragraph(),
            'tipo' => fake()->randomElement(['consulta', 'soporte', 'ventas']),
            'estado' => 'pendiente',
            'leido' => false,
        ];
    }
}
