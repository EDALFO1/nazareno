<x-filament-panels::page>
    @php($personas = $this->personasSinRetomar)
    @php($puntos = $this->puntosSinReportar)
    @php($cumpleanos = $this->cumpleanosDelMes)

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Personas sin retomar</div>
            <div class="text-3xl font-bold {{ $personas->isEmpty() ? 'text-gray-400 dark:text-gray-500' : 'text-danger-600 dark:text-danger-400' }}">
                {{ $personas->count() }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Puntos sin reportar</div>
            <div class="text-3xl font-bold {{ $puntos->isEmpty() ? 'text-gray-400 dark:text-gray-500' : 'text-danger-600 dark:text-danger-400' }}">
                {{ $puntos->count() }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Cumpleaños este mes</div>
            <div class="text-3xl font-bold {{ $cumpleanos->isEmpty() ? 'text-gray-400 dark:text-gray-500' : 'text-info-600 dark:text-info-400' }}">
                {{ $cumpleanos->count() }}
            </div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Personas sin retomar hace 30+ días" description="Estado 'Nuevo' o 'En seguimiento' sin nota de seguimiento reciente.">
        @if ($personas->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay personas pendientes de retomar. 🎉</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">Nombre</th>
                            <th class="py-2 pr-4">Teléfono</th>
                            <th class="py-2 pr-4">Estado</th>
                            <th class="py-2 pr-4">Sin retomar hace</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personas as $persona)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4 font-medium">{{ $persona->nombre_completo }}</td>
                                <td class="py-2 pr-4">{{ $persona->telefono ?? '—' }}</td>
                                <td class="py-2 pr-4">
                                    <x-filament::badge :color="$persona->estado === 'nuevo' ? 'info' : 'warning'">
                                        {{ $persona->estado === 'nuevo' ? 'Nuevo' : 'En seguimiento' }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2 pr-4 text-danger-600 dark:text-danger-400">
                                    {{ $persona->dias_sin_retomar !== null ? "{$persona->dias_sin_retomar} días" : 'Nunca' }}
                                </td>
                                <td class="py-2">
                                    <a href="{{ route('filament.admin.resources.personas.edit', $persona) }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                        Ver persona
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section heading="Puntos de conexión sin reportar hace 2+ semanas" description="Puntos activos sin una reunión registrada en los últimos 14 días.">
        @if ($puntos->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Todos los puntos activos están al día. 🎉</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">Punto de conexión</th>
                            <th class="py-2 pr-4">Red</th>
                            <th class="py-2 pr-4">Líder</th>
                            <th class="py-2 pr-4">Sin reportar hace</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($puntos as $punto)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4 font-medium">{{ $punto->nombre }}</td>
                                <td class="py-2 pr-4">{{ $punto->red?->nombre ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ $punto->lider?->nombre_completo ?? '—' }}</td>
                                <td class="py-2 pr-4 text-danger-600 dark:text-danger-400">
                                    {{ $punto->dias_sin_reportar !== null ? "{$punto->dias_sin_reportar} días" : 'Nunca ha reportado' }}
                                </td>
                                <td class="py-2">
                                    <a href="{{ route('filament.admin.resources.puntos-conexion.edit', $punto) }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                        Ver punto
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section heading="Cumpleaños de este mes">
        @if ($cumpleanos->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Nadie cumple años este mes.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">Día</th>
                            <th class="py-2 pr-4">Nombre</th>
                            <th class="py-2 pr-4">Teléfono</th>
                            <th class="py-2">Red</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cumpleanos as $persona)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4">{{ $persona->fecha_nacimiento->format('d') }}</td>
                                <td class="py-2 pr-4 font-medium">{{ $persona->nombre_completo }}</td>
                                <td class="py-2 pr-4">{{ $persona->telefono ?? '—' }}</td>
                                <td class="py-2">{{ $persona->red?->nombre ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
