<?php

namespace App\Http\Controllers;

use App\Models\CuentaPendiente;
use App\Models\MovimientoContable;
use App\Services\ReporteFinancieroExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteFinancieroController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Reporte financiero';
        $desde = $request->get('desde') ?: now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->get('hasta') ?: now()->endOfMonth()->format('Y-m-d');

        $reporte = $this->reporte($desde, $hasta);

        return view('modules.reporte_financiero.index', compact('titulo', 'desde', 'hasta', 'reporte'));
    }

    public function exportar(Request $request, ReporteFinancieroExportService $servicio)
    {
        $desde = $request->get('desde') ?: now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->get('hasta') ?: now()->endOfMonth()->format('Y-m-d');

        $ruta = $servicio->generar($this->reporte($desde, $hasta), $desde, $hasta);

        return response()->download($ruta, 'reporte-financiero-'.now()->format('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * @return array{
     *     saldoActual: float, totalIngresos: float, totalEgresos: float,
     *     ingresosPorCategoria: Collection, egresosPorCategoria: Collection,
     *     movimientosIngreso: Collection, movimientosEgreso: Collection,
     *     totalPorCobrar: float, totalPorPagar: float,
     * }
     */
    private function reporte(?string $desde, ?string $hasta): array
    {
        $saldoActual = (float) MovimientoContable::where('tipo', 'ingreso')->sum('monto')
            - (float) MovimientoContable::where('tipo', 'egreso')->sum('monto');

        $totalPorCobrar = (float) CuentaPendiente::where('tipo', 'por_cobrar')->get()->sum('saldo_pendiente');
        $totalPorPagar = (float) CuentaPendiente::where('tipo', 'por_pagar')->get()->sum('saldo_pendiente');

        $construirQuery = function (string $tipo) use ($desde, $hasta) {
            $query = MovimientoContable::query()->where('tipo', $tipo);

            if ($desde) {
                $query->whereDate('fecha', '>=', $desde);
            }

            if ($hasta) {
                $query->whereDate('fecha', '<=', $hasta);
            }

            return $query->with(['categoriaContable', 'persona'])->orderBy('fecha');
        };

        $movimientosIngreso = $construirQuery('ingreso')->get();
        $movimientosEgreso = $construirQuery('egreso')->get();

        $agruparPorCategoria = fn (Collection $movimientos) => $movimientos
            ->groupBy(fn (MovimientoContable $m) => $m->categoriaContable?->nombre ?? 'Sin categoría')
            ->map(fn (Collection $grupo, string $nombre) => [
                'categoria' => $nombre,
                'total' => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'saldoActual' => $saldoActual,
            'totalIngresos' => (float) $movimientosIngreso->sum('monto'),
            'totalEgresos' => (float) $movimientosEgreso->sum('monto'),
            'ingresosPorCategoria' => $agruparPorCategoria($movimientosIngreso),
            'egresosPorCategoria' => $agruparPorCategoria($movimientosEgreso),
            'movimientosIngreso' => $movimientosIngreso,
            'movimientosEgreso' => $movimientosEgreso,
            'totalPorCobrar' => $totalPorCobrar,
            'totalPorPagar' => $totalPorPagar,
        ];
    }
}
