<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
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
        'precio_compra',
        'precio_anterior',
        'stock',
        'stock_minimo',
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'especificaciones' => 'array',
            'precio' => 'decimal:2',
            'precio_compra' => 'decimal:2',
            'precio_anterior' => 'decimal:2',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->slug)) {
                $producto->slug = self::generateUniqueSlug($producto->nombre);
            }
        });

        static::updating(function (Producto $producto) {
            // Solo generar slug si está vacío (no cambiar slug existente al editar nombre)
            if (empty($producto->slug)) {
                $producto->slug = self::generateUniqueSlug($producto->nombre, $producto->id);
            }
        });

        static::deleting(function (Producto $producto) {
            // Eliminar imágenes de galería asociadas al producto
            foreach ($producto->imagenes as $imagen) {
                if ($imagen->imagen && Storage::disk('public')->exists($imagen->imagen)) {
                    Storage::disk('public')->delete($imagen->imagen);
                }
                $imagen->delete();
            }
        });

        static::forceDeleting(function (Producto $producto) {
            // Eliminar imágenes de galería asociadas al producto (force delete)
            foreach ($producto->imagenes()->withTrashed()->get() as $imagen) {
                if ($imagen->imagen && Storage::disk('public')->exists($imagen->imagen)) {
                    Storage::disk('public')->delete($imagen->imagen);
                }
                $imagen->forceDelete();
            }
        });
    }

    public static function generateUniqueSlug(string $nombre, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($nombre);
        $slug = $baseSlug;
        $counter = 2;

        $query = self::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
            $query = self::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
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

    public function imagenes(): HasMany
    {
        return $this->hasMany(Galeria::class, 'producto_id')->orderBy('id');
    }
}
