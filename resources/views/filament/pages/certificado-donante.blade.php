<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @php($certificado = $this->certificado)

    @if (! $certificado)
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Selecciona una persona y un año arriba para ver el total donado y generar su certificado.
            </p>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-bold">{{ $certificado['persona']->nombre_completo }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Certificado de donaciones — año {{ $this->anio }}</p>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total donado en efectivo/consignación</div>
                <div class="text-2xl font-bold">
                    ${{ number_format($certificado['totalEfectivo'], 0, ',', '.') }}
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total donado en especie (activos)</div>
                <div class="text-2xl font-bold">
                    ${{ number_format($certificado['totalActivos'], 0, ',', '.') }}
                </div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Detalle de ingresos en efectivo">
            @if ($certificado['movimientos']->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No hay donaciones en efectivo registradas para esta persona en {{ $this->anio }}.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Categoría</th>
                                <th class="py-2 pr-4">Método</th>
                                <th class="py-2">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($certificado['movimientos'] as $movimiento)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2 pr-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                    <td class="py-2 pr-4">{{ $movimiento->categoriaContable?->nombre }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ $movimiento->metodo_pago }}</td>
                                    <td class="py-2">${{ number_format((float) $movimiento->monto, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Detalle de donaciones en especie">
            @if ($certificado['donacionesActivos']->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No hay donaciones en especie registradas para esta persona en {{ $this->anio }}.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4">Fecha</th>
                                <th class="py-2 pr-4">Descripción</th>
                                <th class="py-2">Valor estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($certificado['donacionesActivos'] as $donacion)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2 pr-4">{{ $donacion->fecha->format('d/m/Y') }}</td>
                                    <td class="py-2 pr-4">{{ $donacion->descripcion }}</td>
                                    <td class="py-2">
                                        {{ $donacion->valor_estimado ? '$' . number_format((float) $donacion->valor_estimado, 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
