<?php

namespace App\Http\Requests\Admin\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('caracteristicas') && is_string($this->input('caracteristicas'))) {
            $this->merge(['caracteristicas' => json_decode($this->input('caracteristicas'), true) ?? null]);
        }
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caracteristicas' => ['nullable', 'array'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'duracion_estimada' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
        ];
    }
}
