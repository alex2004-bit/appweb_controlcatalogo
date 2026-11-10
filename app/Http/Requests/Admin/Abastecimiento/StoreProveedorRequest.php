<?php

namespace App\Http\Requests\Admin\Abastecimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create_proveedores') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180'],
            'documento' => ['nullable', 'string', 'max:30', Rule::unique('proveedores', 'documento')],
            'contacto' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'activo' => ['required', 'boolean'],
        ];
    }
}
