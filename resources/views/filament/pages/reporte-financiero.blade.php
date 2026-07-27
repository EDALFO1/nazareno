<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @php($reporte = $this->reporte)

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Saldo actual (caja/bancos)</div>
            <div class="text-3xl font-bold {{ $reporte['saldoActual'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                ${{ number_format($reporte['saldoActual'], 0, ',', '.') }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Por cobrar (nos deben)</div>
            <div class="text-3xl font-bold text-info-600 dark:text-info-400">
                ${{ number_format($reporte['totalPorCobrar'], 0, ',', '.') }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Por pagar (debemos)</div>
            <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                ${{ number_format($reporte['totalPorPagar'], 0, ',', '.') }}
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Ingresos del periodo</div>
            <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                ${{ number_format($reporte['totalIngresos'], 0, ',', '.') }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Egresos del periodo</div>
            <div class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                ${{ number_format($reporte['totalEgresos'], 0, ',', '.') }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Balance del periodo</div>
            @php($balancePeriodo = $reporte['totalIngresos'] - $reporte['totalEgresos'])
            <div class="text-2xl font-bold {{ $balancePeriodo >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                ${{ number_format($balancePeriodo, 0, ',', '.') }}
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-filament::section heading="Ingresos por categoría">
            @if ($reporte['ingresosPorCategoria']->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Sin ingresos en el periodo seleccionado.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4">Categoría</th>
                                <th class="py-2 pr-4">Movimientos</th>
                                <th class="py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reporte['ingresosPorCategoria'] as $fila)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2 pr-4">{{ $fila['categoria'] }}</td>
                                    <td class="py-2 pr-4">{{ $fila['cantidad'] }}</td>
                                    <td class="py-2 font-medium">${{ number_format($fila['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Egresos por categoría">
            @if ($reporte['egresosPorCategoria']->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Sin egresos en el periodo seleccionado.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4">Categoría</th>
                                <th class="py-2 pr-4">Movimientos</th>
                                <th class="py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reporte['egresosPorCategoria'] as $fila)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2 pr-4">{{ $fila['categoria'] }}</td>
                                    <td class="py-2 pr-4">{{ $fila['cantidad'] }}</td>
                                    <td class="py-2 font-medium">${{ number_format($fila['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>

    <x-filament::section heading="Detalle de ingresos">
        @if ($reporte['movimientosIngreso']->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Sin ingresos en el periodo seleccionado.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">Fecha</th>
                            <th class="py-2 pr-4">Categoría</th>
                            <th class="py-2 pr-4">Persona</th>
                            <th class="py-2 pr-4">Método</th>
                            <th class="py-2">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reporte['movimientosIngreso'] as $movimiento)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="py-2 pr-4">{{ $movimiento->categoriaContable?->nombre }}</td>
                                <td class="py-2 pr-4">{{ $movimiento->persona?->nombre_completo ?? '—' }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $movimiento->metodo_pago }}</td>
                                <td class="py-2 font-medium text-success-600 dark:text-success-400">
                                    ${{ number_format((float) $movimiento->monto, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section heading="Detalle de egresos">
        @if ($reporte['movimientosEgreso']->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Sin egresos en el periodo seleccionado.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">Fecha</th>
                            <th class="py-2 pr-4">Categoría</th>
                            <th class="py-2 pr-4">Concepto</th>
                            <th class="py-2 pr-4">Método</th>
                            <th class="py-2">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reporte['movimientosEgreso'] as $movimiento)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="py-2 pr-4">{{ $movimiento->categoriaContable?->nombre }}</td>
                                <td class="py-2 pr-4">{{ $movimiento->descripcion ?? '—' }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $movimiento->metodo_pago }}</td>
                                <td class="py-2 font-medium text-danger-600 dark:text-danger-400">
                                    ${{ number_format((float) $movimiento->monto, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
