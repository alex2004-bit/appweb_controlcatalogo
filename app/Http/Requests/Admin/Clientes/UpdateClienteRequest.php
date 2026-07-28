<?php

namespace App\Http\Requests\Admin\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_persona' => 'required|in:natural,juridica',
            'nombre' => 'required|string|max:200',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'documento' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:200',
            'notas' => 'nullable|string|max:1000',
        ];
    }
}
