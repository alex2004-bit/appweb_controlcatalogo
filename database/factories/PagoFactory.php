<?php

namespace Database\Factories;

use App\Models\Cotizacion;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'cotizacion_id' => Cotizacion::factory(),
            'monto' => fake()->randomFloat(2, 50, 500),
            'metodo_pago' => fake()->randomElement(['efectivo', 'transferencia', 'tarjeta']),
            'estado' => 'completado',
            'fecha_pago' => now(),
        ];
    }
}
