<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'metodo_pago' => 'nullable|in:efectivo,transferencia,tarjeta,cheque,deposito,otro',
            'pagado' => 'nullable|boolean',
            'notas' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:producto,servicio',
            'items.*.item_id' => 'required|integer',
            'items.*.item_nombre' => 'required|string|max:200',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ];
    }
}
