<?php

namespace App\Http\Requests\Finanzas;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit_pagos');
    }

    public function rules(): array
    {
        return [
            'monto' => 'required|numeric|min:0.01|max:9999999.99',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta,cheque,deposito,otro',
            'referencia' => 'nullable|string|max:100',
            'fecha_pago' => 'required|date',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notas' => 'nullable|string|max:500',
        ];
    }
}
