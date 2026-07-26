@php
    $persona = $nodo['persona'];
    $hijos = $nodo['hijos'];
    $estadoColor = match ($persona->estado) {
        'nuevo' => 'info',
        'en_seguimiento' => 'warning',
        'en_red' => 'success',
        'inactivo' => 'gray',
        default => 'gray',
    };
    $estadoLabel = match ($persona->estado) {
        'nuevo' => 'Nuevo',
        'en_seguimiento' => 'En seguimiento',
        'en_red' => 'En red',
        'inactivo' => 'Inactivo',
        default => $persona->estado,
    };
@endphp

<li>
    <div class="flex flex-wrap items-center gap-2 py-1">
        @if ($persona->es_lider_principal)
            <x-filament::icon icon="heroicon-s-star" class="h-4 w-4 shrink-0 text-warning-500" />
        @elseif (count($hijos) > 0)
            <x-filament::icon icon="heroicon-o-user-group" class="h-4 w-4 shrink-0 text-gray-400" />
        @else
            <x-filament::icon icon="heroicon-o-user" class="h-4 w-4 shrink-0 text-gray-300" />
        @endif

        <span class="font-medium">{{ $persona->nombre_completo }}</span>

        @if ($persona->etiqueta_linea)
            <x-filament::badge color="gray" size="sm">
                {{ $persona->etiqueta_linea }}
            </x-filament::badge>
        @endif

        <x-filament::badge :color="$estadoColor" size="sm">
            {{ $estadoLabel }}
        </x-filament::badge>

        @if (count($hijos) > 0)
            <span class="text-xs text-gray-400">
                {{ count($hijos) }} {{ count($hijos) === 1 ? 'persona directa' : 'personas directas' }}
            </span>
        @endif
    </div>

    @if (count($hijos) > 0)
        <ul class="ml-2 space-y-1 border-l border-gray-200 pl-4 dark:border-white/10">
            @foreach ($hijos as $hijo)
                @include('filament.pages.partials.nodo-arbol', ['nodo' => $hijo])
            @endforeach
        </ul>
    @endif
</li>
