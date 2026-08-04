<?php

namespace App\Services;

use App\Models\CategoriaContable;
use App\Models\CuentaPendiente;
use App\Models\MovimientoContable;
use Illuminate\Support\Carbon;

/**
 * La iglesia debe girar mensualmente a la iglesia principal el 15% de todos
 * los diezmos recibidos ese mes ("Diezmo de Diezmos"). Cada vez que se
 * crea, edita o elimina un ingreso de categoría "Diezmo", se recalcula desde
 * cero cuánto se debe por ese concepto en el mes correspondiente — no se va
 * sumando/restando incrementalmente, así que nunca se desincroniza aunque
 * se corrija o borre un registro anterior.
 *
 * La obligación queda como una Cuenta Pendiente "por pagar" (una por mes),
 * reutilizando el flujo de abonos ya existente: cuando de verdad se le gira
 * el dinero a la iglesia principal, se registra ahí como un abono normal.
 */
class DiezmoDeDiezmosService
{
    public const PORCENTAJE = 0.15;

    public const CATEGORIA_INGRESO = 'Diezmo';

    public const CATEGORIA_EGRESO = 'Diezmo de Diezmos';

    /**
     * Recalcula la obligación del mes de $fecha y actualiza (o crea) la
     * Cuenta Pendiente correspondiente. Devuelve el monto total vigente
     * para ese mes.
     */
    public function sincronizarMes(Carbon $fecha): float
    {
        $categoriaEgreso = CategoriaContable::where('tipo', 'egreso')
            ->where('nombre', self::CATEGORIA_EGRESO)
            ->first();

        if (! $categoriaEgreso) {
            return 0.0;
        }

        $totalDiezmos = (float) MovimientoContable::query()
            ->where('tipo', 'ingreso')
            ->whereHas('categoriaContable', fn ($q) => $q->where('nombre', self::CATEGORIA_INGRESO))
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->sum('monto');

        $obligacion = round($totalDiezmos * self::PORCENTAJE, 2);

        $cuenta = CuentaPendiente::where('tipo', 'por_pagar')
            ->where('categoria_contable_id', $categoriaEgreso->id)
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->first();

        if (! $cuenta) {
            if ($obligacion <= 0) {
                return 0.0;
            }

            CuentaPendiente::create([
                'tipo' => 'por_pagar',
                'categoria_contable_id' => $categoriaEgreso->id,
                'descripcion' => 'Diezmo de diezmos - '.ucfirst($fecha->translatedFormat('F Y')),
                'monto_total' => $obligacion,
                'fecha' => $fecha->copy()->startOfMonth(),
            ]);

            return $obligacion;
        }

        $cuenta->update(['monto_total' => $obligacion]);

        return $obligacion;
    }

    public function esIngresoDeDiezmo(MovimientoContable $movimiento): bool
    {
        return $movimiento->tipo === 'ingreso'
            && $movimiento->categoriaContable?->nombre === self::CATEGORIA_INGRESO;
    }

    /**
     * Cuenta Pendiente vigente (si existe) para el mes de $fecha.
     */
    public function cuentaDelMes(Carbon $fecha): ?CuentaPendiente
    {
        $categoriaEgreso = CategoriaContable::where('tipo', 'egreso')
            ->where('nombre', self::CATEGORIA_EGRESO)
            ->first();

        if (! $categoriaEgreso) {
            return null;
        }

        return CuentaPendiente::where('tipo', 'por_pagar')
            ->where('categoria_contable_id', $categoriaEgreso->id)
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->first();
    }

    public function totalDiezmosDelMes(Carbon $fecha): float
    {
        return (float) MovimientoContable::query()
            ->where('tipo', 'ingreso')
            ->whereHas('categoriaContable', fn ($q) => $q->where('nombre', self::CATEGORIA_INGRESO))
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->sum('monto');
    }
}
