<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'cliente_id',
        'created_by',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'estado',
        'metodo_pago',
        'pagado',
        'notas',
        'completado_at',
        'cancelado_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'pagado' => 'boolean',
            'completado_at' => 'datetime',
            'cancelado_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function getSaldoPendienteAttribute(): float
    {
        return $this->pagado ? 0 : (float) $this->total;
    }

    public function getEstadoPagoAttribute(): string
    {
        return $this->pagado ? 'pagado' : 'pendiente';
    }
}
