@php
    $persona = $nodo['persona'];
    $hijos = $nodo['hijos'];
    $colores = ['nuevo' => 'info', 'en_seguimiento' => 'warning', 'en_red' => 'success', 'inactivo' => 'secondary'];
    $etiquetas = ['nuevo' => 'Nuevo', 'en_seguimiento' => 'En seguimiento', 'en_red' => 'En red', 'inactivo' => 'Inactivo'];
@endphp

<li>
    <div class="d-flex flex-wrap align-items-center gap-2 py-1">
        @if($persona->es_lider_principal)
            <i class="bi bi-star-fill text-warning"></i>
        @elseif(count($hijos) > 0)
            <i class="bi bi-people text-muted"></i>
        @else
            <i class="bi bi-person text-muted opacity-50"></i>
        @endif

        <span class="fw-semibold">{{ $persona->nombre_completo }}</span>

        @if($persona->etiqueta_linea)
            <span class="badge bg-light text-dark border">{{ $persona->etiqueta_linea }}</span>
        @endif

        <span class="badge bg-{{ $colores[$persona->estado] }}">{{ $etiquetas[$persona->estado] }}</span>

        @if(count($hijos) > 0)
            <span class="text-muted small">{{ count($hijos) }} {{ count($hijos) === 1 ? 'persona directa' : 'personas directas' }}</span>
        @endif
    </div>

    @if(count($hijos) > 0)
        <ul class="ms-2 ps-3 border-start">
            @foreach($hijos as $hijo)
                @include('modules.estructura_red.nodo_arbol', ['nodo' => $hijo])
            @endforeach
        </ul>
    @endif
</li>
