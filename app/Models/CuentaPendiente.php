<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tipo', 'categoria_contable_id', 'persona_id', 'descripcion',
    'monto_total', 'fecha', 'fecha_vencimiento', 'notas',
])]
class CuentaPendiente extends Model
{
    protected $table = 'cuentas_pendientes';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_vencimiento' => 'date',
            'monto_total' => 'decimal:2',
        ];
    }

    public function categoriaContable(): BelongsTo
    {
        return $this->belongsTo(CategoriaContable::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Movimientos (abonos/pagos) ya registrados contra esta cuenta pendiente.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class);
    }

    protected function montoPagado(): Attribute
    {
        return Attribute::get(fn () => (float) $this->movimientos()->sum('monto'));
    }

    protected function saldoPendiente(): Attribute
    {
        return Attribute::get(fn () => (float) $this->monto_total - $this->monto_pagado);
    }

    /**
     * pendiente | parcial | pagada | vencida — calculado, no se guarda en BD
     * para que nunca se desincronice de los movimientos reales.
     */
    protected function estado(): Attribute
    {
        return Attribute::get(function () {
            if ($this->saldo_pendiente <= 0) {
                return 'pagada';
            }

            if ($this->monto_pagado > 0) {
                return 'parcial';
            }

            if ($this->fecha_vencimiento?->isPast()) {
                return 'vencida';
            }

            return 'pendiente';
        });
    }
}
