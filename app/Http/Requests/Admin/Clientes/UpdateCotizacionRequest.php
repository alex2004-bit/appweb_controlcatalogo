<?php

namespace App\Http\Requests\Admin\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'estado' => 'required|in:borrador,enviada,aprobada,rechazada',
            'validez_dias' => 'nullable|integer|min:1|max:365',
            'notas' => 'nullable|string|max:1000',
            'condiciones' => 'nullable|string|max:1000',
            'observaciones' => 'nullable|string|max:1000',
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
