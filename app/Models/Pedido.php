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
        'cotizacion_id',
        'created_by',
        'vendedor_id',
        'referido_por',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'anticipo',
        'numero_cuotas',
        'plazo_dias',
        'estado',
        'metodo_pago',
        'pagado',
        'notas',
        'direccion_entrega',
        'ciudad',
        'provincia',
        'tipo_entrega',
        'fecha_entrega',
        'telefono_contacto',
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
            'anticipo' => 'decimal:2',
            'pagado' => 'boolean',
            'fecha_entrega' => 'datetime',
            'completado_at' => 'datetime',
            'cancelado_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function getSaldoPendienteAttribute(): float
    {
        if ($this->pagado) {
            return 0;
        }

        return (float) $this->total - (float) $this->anticipo;
    }

    public function getEstadoPagoAttribute(): string
    {
        return $this->pagado ? 'pagado' : 'pendiente';
    }
}
