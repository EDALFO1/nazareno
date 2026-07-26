<x-filament-panels::page>
    @php($esVistaPropia = $this->esVistaPropia())

    @if (! $esVistaPropia)
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>
    @endif

    @php($estructura = $this->estructura)

    @if (! $estructura)
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if ($esVistaPropia)
                    Tu usuario no está vinculado todavía a una persona en el sistema, así que no podemos mostrar tu
                    rama. Pídele a un administrador que te vincule desde Personas → tu registro → "Usuario del sistema".
                @else
                    Selecciona cualquier líder arriba (principal o de un grupo) para ver solo su rama: los líderes y
                    personas que dependen de él o ella (a cualquier profundidad) y los puntos de conexión que lideran.
                @endif
            </p>
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Personas en la rama</div>
                <div class="text-2xl font-bold">{{ $estructura['resumen']['personas'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Líderes en la rama</div>
                <div class="text-2xl font-bold">{{ $estructura['resumen']['lideres'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Puntos de conexión</div>
                <div class="text-2xl font-bold">{{ $estructura['resumen']['puntos'] }}</div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Árbol de discipulado">
            <ul class="space-y-1">
                @include('filament.pages.partials.nodo-arbol', ['nodo' => $estructura['arbol']])
            </ul>
        </x-filament::section>

        <x-filament::section heading="Puntos de conexión de la rama">
            @if ($estructura['puntos']->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Nadie en esta rama lidera un punto de conexión todavía.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Líder</th>
                                <th class="py-2 pr-4">Anfitrión</th>
                                <th class="py-2 pr-4">Día</th>
                                <th class="py-2 pr-4">Hora</th>
                                <th class="py-2">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estructura['puntos'] as $punto)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2 pr-4 font-medium">{{ $punto->nombre }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center gap-1">
                                            @if ($punto->lider?->es_lider_principal)
                                                <x-filament::icon icon="heroicon-s-star" class="h-4 w-4 text-warning-500" />
                                            @endif
                                            {{ $punto->lider?->nombre_completo }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4">{{ $punto->anfitrion?->nombre_completo }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ $punto->dia_semana }}</td>
                                    <td class="py-2 pr-4">{{ $punto->hora ? \Illuminate\Support\Carbon::parse($punto->hora)->format('h:i A') : null }}</td>
                                    <td class="py-2">
                                        @if ($punto->activo)
                                            <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 text-success-500" />
                                        @else
                                            <x-filament::icon icon="heroicon-o-x-circle" class="h-5 w-5 text-gray-400" />
                                        @endif
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
