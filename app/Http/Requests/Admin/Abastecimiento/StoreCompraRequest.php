<?php

namespace App\Http\Requests\Admin\Abastecimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create_compras') ?? false;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => [
                'required',
                Rule::exists('proveedores', 'id')->where('activo', true)->whereNull('deleted_at'),
            ],
            'fecha_compra' => ['required', 'date'],
            'numero_factura' => ['nullable', 'string', 'max:80'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')->where('activo', true)->whereNull('deleted_at'),
            ],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}
