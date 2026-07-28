<?php

namespace App\Http\Requests\Admin\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'imagen' => ['nullable', 'string', 'max:255'],
            'caracteristicas' => ['nullable', 'string'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'duracion_estimada' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
        ];
    }
}
