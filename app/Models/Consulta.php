<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'empresa',
        'mensaje',
        'tipo',
        'estado',
        'leido',
        'leido_at',
        'leido_by',
        'cliente_id',
    ];

    protected function casts(): array
    {
        return [
            'leido' => 'boolean',
            'leido_at' => 'datetime',
        ];
    }

    public function leidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leido_by');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
