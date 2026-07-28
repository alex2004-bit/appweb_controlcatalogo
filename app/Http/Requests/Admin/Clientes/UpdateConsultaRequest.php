<?php

namespace App\Http\Requests\Admin\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:pendiente,en_proceso,respondido,cerrado',
            'notas' => 'nullable|string|max:1000',
        ];
    }
}
