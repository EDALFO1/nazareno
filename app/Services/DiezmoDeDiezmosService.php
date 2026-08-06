<?php

namespace App\Services;

use App\Models\CategoriaContable;
use App\Models\CuentaPendiente;
use App\Models\MovimientoContable;
use Illuminate\Support\Carbon;

/**
 * La iglesia debe girar mensualmente a la iglesia principal el 15% de los
 * ingresos por Diezmo y por Ofrenda general recibidos ese mes ("Diezmo de
 * Diezmos"). Cada vez que se crea, edita o elimina un ingreso de esas
 * categorías, se recalcula desde cero cuánto se debe por ese concepto en el
 * mes correspondiente — no se va sumando/restando incrementalmente, así que
 * nunca se desincroniza aunque se corrija o borre un registro anterior.
 *
 * La obligación queda como una Cuenta Pendiente "por pagar" (una por mes).
 * Se puede saldar de dos formas, ambas quedan reflejadas en el saldo:
 * registrando un abono desde la propia Cuenta Pendiente, o registrando
 * directamente un egreso de categoría "Diezmo de Diezmos" desde Movimientos
 * contables — en ese segundo caso, el egreso se vincula automáticamente a
 * la cuenta pendiente del mes (ver vincularPagoSiCorresponde()).
 */
class DiezmoDeDiezmosService
{
    public const PORCENTAJE = 0.15;

    /**
     * Categorías de ingreso sobre las que aplica el 15%.
     *
     * @var array<int, string>
     */
    public const CATEGORIAS_INGRESO = ['Diezmo', 'Ofrenda general'];

    public const CATEGORIA_EGRESO = 'Diezmo de Diezmos';

    /**
     * Recalcula la obligación del mes de $fecha y actualiza (o crea) la
     * Cuenta Pendiente correspondiente. Devuelve el monto total vigente
     * para ese mes.
     */
    public function sincronizarMes(Carbon $fecha): float
    {
        $categoriaEgreso = $this->categoriaEgreso();

        if (! $categoriaEgreso) {
            return 0.0;
        }

        $obligacion = round($this->totalBaseDelMes($fecha) * self::PORCENTAJE, 2);

        $cuenta = $this->cuentaDelMes($fecha);

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

    public function esIngresoBase(MovimientoContable $movimiento): bool
    {
        return $movimiento->tipo === 'ingreso'
            && in_array($movimiento->categoriaContable?->nombre, self::CATEGORIAS_INGRESO, true);
    }

    /**
     * Si $movimiento es un egreso de la categoría "Diezmo de Diezmos" y
     * todavía no está vinculado a ninguna Cuenta Pendiente, lo vincula a la
     * más antigua que siga con saldo pendiente, para que el pago se
     * descuente sin necesidad de pasar por el formulario de abonos. No se
     * limita al mes de la fecha del egreso: casi siempre el pago de "lo que
     * se debe de julio" se hace en agosto o después, así que hay que
     * aplicarlo a la obligación más vieja que siga abierta, no a la del mes
     * en que se paga.
     */
    public function vincularPagoSiCorresponde(MovimientoContable $movimiento): void
    {
        if ($movimiento->tipo !== 'egreso'
            || $movimiento->categoriaContable?->nombre !== self::CATEGORIA_EGRESO
            || $movimiento->cuenta_pendiente_id !== null) {
            return;
        }

        $categoriaEgreso = $this->categoriaEgreso();

        if (! $categoriaEgreso) {
            return;
        }

        $cuenta = CuentaPendiente::where('tipo', 'por_pagar')
            ->where('categoria_contable_id', $categoriaEgreso->id)
            ->orderBy('fecha')
            ->get()
            ->first(fn (CuentaPendiente $c) => $c->saldo_pendiente > 0);

        if ($cuenta) {
            $movimiento->update(['cuenta_pendiente_id' => $cuenta->id]);
        }
    }

    /**
     * Cuenta Pendiente vigente (si existe) para el mes de $fecha.
     */
    public function cuentaDelMes(Carbon $fecha): ?CuentaPendiente
    {
        $categoriaEgreso = $this->categoriaEgreso();

        if (! $categoriaEgreso) {
            return null;
        }

        return CuentaPendiente::where('tipo', 'por_pagar')
            ->where('categoria_contable_id', $categoriaEgreso->id)
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->first();
    }

    /**
     * Total de ingresos de las categorías base (Diezmo + Ofrenda general)
     * del mes de $fecha — la base sobre la que se calcula el 15%.
     */
    public function totalBaseDelMes(Carbon $fecha): float
    {
        return (float) MovimientoContable::query()
            ->where('tipo', 'ingreso')
            ->whereHas('categoriaContable', fn ($q) => $q->whereIn('nombre', self::CATEGORIAS_INGRESO))
            ->whereYear('fecha', $fecha->year)
            ->whereMonth('fecha', $fecha->month)
            ->sum('monto');
    }

    private function categoriaEgreso(): ?CategoriaContable
    {
        return CategoriaContable::where('tipo', 'egreso')
            ->where('nombre', self::CATEGORIA_EGRESO)
            ->first();
    }
}
