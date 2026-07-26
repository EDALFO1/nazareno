<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoContable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FinanzasStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'admin_general']);
    }

    protected function getStats(): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $ingresosMes = MovimientoContable::query()
            ->where('tipo', 'ingreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $egresosMes = MovimientoContable::query()
            ->where('tipo', 'egreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $totalIngresos = MovimientoContable::query()->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = MovimientoContable::query()->where('tipo', 'egreso')->sum('monto');
        $saldoAcumulado = $totalIngresos - $totalEgresos;

        return [
            Stat::make('Ingresos del mes', '$' . number_format((float) $ingresosMes, 0, ',', '.'))
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Egresos del mes', '$' . number_format((float) $egresosMes, 0, ',', '.'))
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),
            Stat::make('Saldo acumulado', '$' . number_format((float) $saldoAcumulado, 0, ',', '.'))
                ->color($saldoAcumulado >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes')
                ->description('Ingresos totales menos egresos totales'),
        ];
    }
}
