<?php

namespace App\Models;

use Database\Factories\CompraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    /** @use HasFactory<CompraFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo',
        'proveedor_id',
        'created_by',
        'fecha_compra',
        'numero_factura',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'notas',
        'recibida_at',
        'cancelada_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
            'recibida_at' => 'datetime',
            'cancelada_at' => 'datetime',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class)->withTrashed();
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
