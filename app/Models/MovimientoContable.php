<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tipo', 'categoria_contable_id', 'fecha', 'monto', 'metodo_pago',
    'persona_id', 'proveedor_id', 'red_id', 'punto_conexion_id', 'cuenta_bancaria_id', 'cuenta_pendiente_id',
    'descripcion', 'referencia', 'comprobante', 'registrado_por_id',
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

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function puntoConexion(): BelongsTo
    {
        return $this->belongsTo(PuntoConexion::class);
    }

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class);
    }

    public function cuentaPendiente(): BelongsTo
    {
        return $this->belongsTo(CuentaPendiente::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    public static function rules($id = null): array
    {
        return [
            'tipo' => ['required', 'in:ingreso,egreso'],
            'categoria_contable_id' => ['required', 'exists:categorias_contables,id'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:efectivo,consignacion,transferencia,cheque'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'persona_id' => ['nullable', 'exists:personas,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
            'red_id' => ['nullable', 'exists:redes,id'],
            'punto_conexion_id' => ['nullable', 'exists:puntos_conexion,id'],
            'cuenta_bancaria_id' => ['nullable', 'exists:cuentas_bancarias,id'],
            'descripcion' => ['nullable', 'string'],
            'comprobante' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
