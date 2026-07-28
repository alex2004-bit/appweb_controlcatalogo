<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CotizacionFactory extends Factory
{
    protected $model = Cotizacion::class;

    public function definition(): array
    {
        return [
            'codigo' => 'COT-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'cliente_id' => Cliente::factory(),
            'created_by' => User::factory(),
            'subtotal' => 100.00,
            'impuesto' => 18.00,
            'total' => 118.00,
            'estado' => 'pendiente',
        ];
    }
}
