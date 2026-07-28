<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'marca_id',
        'nombre',
        'slug',
        'descripcion',
        'especificaciones',
        'sku',
        'precio',
        'precio_anterior',
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'especificaciones' => 'array',
            'precio' => 'decimal:2',
            'precio_anterior' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->slug)) {
                $producto->slug = Str::slug($producto->nombre);
            }
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function detalles(): MorphMany
    {
        return $this->morphMany(DetallePedido::class, 'item');
    }
}
