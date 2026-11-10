<?php

namespace App\Http\Requests\Admin\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'moneda' => 'required|string|max:10',
            'impuesto' => 'required|numeric|min:0|max:100',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'qr_pago' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner_imagen_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner_imagen_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner_imagen_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
