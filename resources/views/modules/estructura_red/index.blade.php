@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-share me-2"></i>Estructura de red</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Estructura de red</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">

@unless($esVistaPropia)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('estructura-red.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">Líder</label>
                <select name="lider" class="form-select select2" onchange="this.form.submit()">
                    <option value="">Selecciona un líder…</option>
                    @foreach($opcionesLideres as $id => $etiqueta)
                        <option value="{{ $id }}" @selected($liderId == $id)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
@endunless

@if(! $estructura)
<div class="card border-0 shadow-sm">
    <div class="card-body text-muted">
        @if($esVistaPropia)
            Tu usuario no está vinculado todavía a una persona en el sistema, así que no podemos mostrar tu rama.
            Pídele a un administrador que te vincule desde Personas → tu registro → "Usuario del sistema".
        @else
            Selecciona cualquier líder arriba (principal o de un grupo) para ver solo su rama: los líderes y personas
            que dependen de él o ella (a cualquier profundidad) y los puntos de conexión que lideran.
        @endif
    </div>
</div>
@else
<div class="row g-3 mb-1">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Personas en la rama</div>
                <div class="fs-3 fw-bold">{{ $estructura['resumen']['personas'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Líderes en la rama</div>
                <div class="fs-3 fw-bold">{{ $estructura['resumen']['lideres'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Puntos de conexión</div>
                <div class="fs-3 fw-bold">{{ $estructura['resumen']['puntos'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Árbol de discipulado</div>
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            @include('modules.estructura_red.nodo_arbol', ['nodo' => $estructura['arbol']])
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Puntos de conexión de la rama</div>
    <div class="card-body p-0">
        @if($estructura['puntos']->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Nadie en esta rama lidera un punto de conexión todavía.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Líder</th>
                        <th>Anfitrión</th>
                        <th>Día</th>
                        <th>Hora</th>
                        <th class="text-center">Activo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estructura['puntos'] as $punto)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $punto->nombre }}</td>
                        <td>
                            @if($punto->lider?->es_lider_principal)<i class="bi bi-star-fill text-warning"></i>@endif
                            {{ $punto->lider?->nombre_completo }}
                        </td>
                        <td>{{ $punto->anfitrion?->nombre_completo }}</td>
                        <td class="text-capitalize">{{ $punto->dia_semana }}</td>
                        <td>{{ $punto->hora ? \Illuminate\Support\Carbon::parse($punto->hora)->format('h:i A') : null }}</td>
                        <td class="text-center">
                            <i class="bi {{ $punto->activo ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

</section>

@push('scripts')
<script>
$(function () { $('.select2').select2({ theme: 'default', width: '100%' }); });
</script>
@endpush

@endsection
