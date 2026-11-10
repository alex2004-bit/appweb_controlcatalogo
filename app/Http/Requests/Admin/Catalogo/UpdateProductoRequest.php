<?php

namespace App\Http\Requests\Admin\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('especificaciones') && is_string($this->input('especificaciones'))) {
            $decoded = json_decode($this->input('especificaciones'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->merge(['especificaciones' => '__INVALID_JSON__']);
            } else {
                $this->merge(['especificaciones' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $producto = $this->route('producto');

        return [
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('activo', true)],
            'marca_id' => ['nullable', 'integer', Rule::exists('marcas', 'id')->where('activo', true)],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'especificaciones' => ['nullable', 'array', 'not_in:__INVALID_JSON__'],
            'especificaciones.*.nombre' => ['required_with:especificaciones', 'string', 'max:255'],
            'especificaciones.*.valor' => ['nullable', 'string', 'max:5000'],
            'especificaciones.*.imagen' => ['nullable', 'string', 'max:500'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('productos', 'sku')->ignore($this->route('producto'))],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_anterior' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'imagenes' => ['nullable', 'array', 'max:12'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'eliminar_imagenes' => ['nullable', 'array', 'max:12'],
            'eliminar_imagenes.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('galeria', 'id')->where('producto_id', $producto->id),
            ],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'especificaciones.not_in' => 'El campo especificaciones contiene JSON inválido.',
            'categoria_id.exists' => 'La categoría seleccionada no existe o no está activa.',
            'marca_id.exists' => 'La marca seleccionada no existe o no está activa.',
        ];
    }
}
