<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'vendedor_id' => 'nullable|exists:users,id',
            'referido_por' => 'nullable|string|max:120',
            'metodo_pago' => 'nullable|in:efectivo,transferencia,tarjeta,cheque,deposito,otro',
            'pagado' => 'nullable|boolean',
            'notas' => 'nullable|string|max:1000',
            'direccion_entrega' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'provincia' => 'nullable|string|max:120',
            'tipo_entrega' => 'nullable|in:tienda,provincia,recoger,domicilio',
            'fecha_entrega' => 'nullable|date',
            'telefono_contacto' => 'nullable|string|max:30',
            'anticipo' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'nullable|integer|min:1|max:24',
            'plazo_dias' => 'nullable|integer|min:0|max:3650',
            'items' => 'required|array|min:1|max:50',
            'items.*.item_type' => ['required', Rule::in(['producto', 'servicio'])],
            'items.*.item_id' => 'required|integer|min:1',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ];
    }
}
