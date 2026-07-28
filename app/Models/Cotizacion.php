<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'codigo',
        'cliente_id',
        'created_by',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'estado',
        'notas',
        'condiciones',
        'observaciones',
        'pdf_generado',
        'validez_dias',
        'aprobada_at',
        'rechazada_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'aprobada_at' => 'datetime',
            'rechazada_at' => 'datetime',
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
        return $this->hasMany(DetalleCotizacion::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function getSaldoPendienteAttribute(): float
    {
        $totalPagado = $this->relationLoaded('pagos')
            ? $this->pagos->sum('monto')
            : $this->pagos()->sum('monto');

        return max(0, (float) $this->total - (float) $totalPagado);
    }

    public function getEstadoPagoAttribute(): string
    {
        $totalPagado = $this->relationLoaded('pagos')
            ? $this->pagos->sum('monto')
            : $this->pagos()->sum('monto');

        if ((float) $totalPagado <= 0) {
            return 'pendiente';
        }

        if ((float) $totalPagado >= (float) $this->total) {
            return 'pagado';
        }

        return 'parcial';
    }
}
