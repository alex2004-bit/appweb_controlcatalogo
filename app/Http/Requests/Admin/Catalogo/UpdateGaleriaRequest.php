<?php

namespace App\Http\Requests\Admin\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'tipo' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ];
    }
}
