<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'banco', 'numero_cuenta', 'tipo_cuenta', 'saldo_inicial', 'activa'])]
class CuentaBancaria extends Model
{
    protected $table = 'cuentas_bancarias';

    protected function casts(): array
    {
        return [
            'saldo_inicial' => 'decimal:2',
            'activa' => 'boolean',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class);
    }

    protected function saldoActual(): Attribute
    {
        return Attribute::get(function () {
            $ingresos = (float) $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
            $egresos = (float) $this->movimientos()->where('tipo', 'egreso')->sum('monto');

            return (float) $this->saldo_inicial + $ingresos - $egresos;
        });
    }
}
