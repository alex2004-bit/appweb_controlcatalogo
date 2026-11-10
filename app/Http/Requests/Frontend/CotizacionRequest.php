<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'email' => 'nullable|email|max:255',
            'telefono' => 'required|string|max:30',
            'documento' => 'nullable|string|max:20',
            'direccion' => 'exclude_unless:tipo_entrega,provincia,domicilio|required|string|max:255',
            'ciudad' => 'exclude_unless:tipo_entrega,provincia,domicilio|required|string|max:120',
            'provincia' => 'exclude_unless:tipo_entrega,provincia|required|string|max:120',
            'tipo_entrega' => 'required|in:tienda,provincia,domicilio,recoger',
            'notas' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:20',
            'items.*.id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1|max:99',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Datos de validación inválidos.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
