<?php

namespace App\Filament\Pages;

use App\Models\CuentaPendiente;
use App\Models\MovimientoContable;
use App\Services\ReporteFinancieroExportService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteFinanciero extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Reporte financiero';

    protected static ?string $title = 'Reporte financiero';

    protected static ?string $slug = 'reporte-financiero';

    protected static string $view = 'filament.pages.reporte-financiero';

    public static function canAccess(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'admin_general']);
    }

    public ?string $desde = null;

    public ?string $hasta = null;

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'desde' => $this->desde,
            'hasta' => $this->hasta,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                DatePicker::make('desde')
                    ->label('Desde')
                    ->native(false)
                    ->live(),
                DatePicker::make('hasta')
                    ->label('Hasta')
                    ->native(false)
                    ->live(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportarExcel()),
        ];
    }

    public function exportarExcel(): BinaryFileResponse
    {
        $ruta = app(ReporteFinancieroExportService::class)->generar($this->reporte(), $this->desde, $this->hasta);

        $nombreArchivo = 'reporte-financiero-'.now()->format('Y-m-d').'.xlsx';

        return response()->download($ruta, $nombreArchivo)->deleteFileAfterSend(true);
    }

    /**
     * @return array{
     *     saldoActual: float,
     *     totalIngresos: float,
     *     totalEgresos: float,
     *     ingresosPorCategoria: Collection,
     *     egresosPorCategoria: Collection,
     *     movimientosIngreso: Collection,
     *     movimientosEgreso: Collection,
     *     totalPorCobrar: float,
     *     totalPorPagar: float,
     * }
     */
    #[Computed]
    public function reporte(): array
    {
        // El saldo actual es histórico (caja real), no depende del filtro de fechas.
        $saldoActual = (float) MovimientoContable::where('tipo', 'ingreso')->sum('monto')
            - (float) MovimientoContable::where('tipo', 'egreso')->sum('monto');

        // Cuentas por cobrar/pagar tampoco dependen del filtro: es lo pendiente hoy.
        $totalPorCobrar = (float) CuentaPendiente::where('tipo', 'por_cobrar')->get()->sum('saldo_pendiente');
        $totalPorPagar = (float) CuentaPendiente::where('tipo', 'por_pagar')->get()->sum('saldo_pendiente');

        $construirQuery = function (string $tipo) {
            $query = MovimientoContable::query()->where('tipo', $tipo);

            if ($this->desde) {
                $query->whereDate('fecha', '>=', $this->desde);
            }

            if ($this->hasta) {
                $query->whereDate('fecha', '<=', $this->hasta);
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
