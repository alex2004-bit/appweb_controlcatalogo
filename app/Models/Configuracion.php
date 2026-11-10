<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre_empresa',
        'ruc',
        'logo',
        'qr_pago',
        'banner_imagen_1',
        'banner_imagen_2',
        'banner_imagen_3',
        'telefono',
        'email',
        'website',
        'direccion',
        'moneda',
        'impuesto',
        'whatsapp',
        'facebook',
        'instagram',
    ];

    public static function impuestoRate(): float
    {
        $impuesto = (float) (static::query()->first()?->impuesto ?? 18);

        return $impuesto / 100;
    }

    public static function moneda(): string
    {
        return static::query()->first()?->moneda ?: 'Bs';
    }
}
