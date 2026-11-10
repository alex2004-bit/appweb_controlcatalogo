<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'codigo' => 'PED-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'cliente_id' => Cliente::factory(),
            'created_by' => User::factory(),
            'subtotal' => 100.00,
            'impuesto' => 18.00,
            'descuento' => 0,
            'total' => 118.00,
            'anticipo' => 0,
            'numero_cuotas' => 1,
            'plazo_dias' => null,
            'estado' => 'pendiente',
            'pagado' => false,
        ];
    }
}
