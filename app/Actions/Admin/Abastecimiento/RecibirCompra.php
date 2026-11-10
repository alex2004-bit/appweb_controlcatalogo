<?php

namespace App\Actions\Admin\Abastecimiento;

use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecibirCompra
{
    public function execute(Compra $compra): Compra
    {
        return DB::transaction(function () use ($compra): Compra {
            $compra = Compra::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($compra->id);

            if ($compra->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'compra' => 'Esta compra ya fue recibida o cancelada.',
                ]);
            }

            foreach ($compra->detalles->sortBy('producto_id') as $detalle) {
                $producto = Producto::query()->lockForUpdate()->find($detalle->producto_id);

                if (! $producto || ! $producto->activo) {
                    throw ValidationException::withMessages([
                        'compra' => "El producto {$detalle->producto_nombre} ya no está disponible. Reactívalo antes de recibir la compra.",
                    ]);
                }

                $producto->update([
                    'stock' => ($producto->stock ?? 0) + $detalle->cantidad,
                    'precio_compra' => $detalle->precio_unitario,
                ]);
            }

            $compra->update([
                'estado' => 'recibida',
                'recibida_at' => now(),
            ]);

            return $compra->refresh();
        }, attempts: 3);
    }
}
