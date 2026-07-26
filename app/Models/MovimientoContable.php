<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tipo', 'categoria_contable_id', 'fecha', 'monto', 'metodo_pago',
    'persona_id', 'red_id', 'punto_conexion_id', 'descripcion', 'referencia',
    'comprobante', 'registrado_por_id',
])]
class MovimientoContable extends Model
{
    protected $table = 'movimientos_contables';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
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

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function puntoConexion(): BelongsTo
    {
        return $this->belongsTo(PuntoConexion::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
