<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Servicio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'slug',
        'descripcion',
        'imagen',
        'caracteristicas',
        'precio',
        'duracion_estimada',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'caracteristicas' => 'array',
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Servicio $servicio) {
            if (empty($servicio->slug)) {
                $servicio->slug = Str::slug($servicio->nombre);
            }
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
