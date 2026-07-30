@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-bell-fill me-2"></i>Alertas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Alertas</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">

<div class="row g-3 mb-1">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Personas sin retomar</div>
                <div class="fs-2 fw-bold {{ $personasSinRetomar->isEmpty() ? 'text-muted' : 'text-danger' }}">{{ $personasSinRetomar->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Puntos sin reportar</div>
                <div class="fs-2 fw-bold {{ $puntosSinReportar->isEmpty() ? 'text-muted' : 'text-danger' }}">{{ $puntosSinReportar->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Cumpleaños este mes</div>
                <div class="fs-2 fw-bold {{ $cumpleanosDelMes->isEmpty() ? 'text-muted' : 'text-info' }}">{{ $cumpleanosDelMes->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">
        Personas sin retomar hace 30+ días
        <small class="text-muted d-block fw-normal">Estado "Nuevo" o "En seguimiento" sin nota de seguimiento reciente.</small>
    </div>
    <div class="card-body p-0">
        @if($personasSinRetomar->isEmpty())
            <p class="text-muted text-center py-4 mb-0">No hay personas pendientes de retomar. 🎉</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-3">Nombre</th><th>Teléfono</th><th>Estado</th><th>Sin retomar hace</th><th class="text-center" style="width:80px"></th></tr>
                </thead>
                <tbody>
                    @foreach($personasSinRetomar as $persona)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $persona->nombre_completo }}</td>
                        <td>{{ $persona->telefono ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $persona->estado === 'nuevo' ? 'bg-info' : 'bg-warning' }}">
                                {{ $persona->estado === 'nuevo' ? 'Nuevo' : 'En seguimiento' }}
                            </span>
                        </td>
                        <td class="text-danger">{{ $persona->dias_sin_retomar !== null ? "{$persona->dias_sin_retomar} días" : 'Nunca' }}</td>
                        <td class="text-center">
                            <a href="{{ route('personas.show', $persona->id) }}" class="btn btn-outline-primary btn-sm">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">
        Puntos de conexión sin reportar hace 2+ semanas
        <small class="text-muted d-block fw-normal">Puntos activos sin una reunión registrada en los últimos 14 días.</small>
    </div>
    <div class="card-body p-0">
        @if($puntosSinReportar->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Todos los puntos activos están al día. 🎉</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-3">Punto de conexión</th><th>Red</th><th>Líder</th><th>Sin reportar hace</th><th class="text-center" style="width:80px"></th></tr>
                </thead>
                <tbody>
                    @foreach($puntosSinReportar as $punto)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $punto->nombre }}</td>
                        <td>{{ $punto->red?->nombre ?? '—' }}</td>
                        <td>{{ $punto->lider?->nombre_completo ?? '—' }}</td>
                        <td class="text-danger">{{ $punto->dias_sin_reportar !== null ? "{$punto->dias_sin_reportar} días" : 'Nunca ha reportado' }}</td>
                        <td class="text-center">
                            <a href="{{ route('puntos_conexion.show', $punto->id) }}" class="btn btn-outline-primary btn-sm">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Cumpleaños de este mes</div>
    <div class="card-body p-0">
        @if($cumpleanosDelMes->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Nadie cumple años este mes.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-3">Día</th><th>Nombre</th><th>Teléfono</th><th>Red</th></tr>
                </thead>
                <tbody>
                    @foreach($cumpleanosDelMes as $persona)
                    <tr>
                        <td class="ps-3">{{ $persona->fecha_nacimiento->format('d') }}</td>
                        <td class="fw-semibold">{{ $persona->nombre_completo }}</td>
                        <td>{{ $persona->telefono ?? '—' }}</td>
                        <td>{{ $persona->red?->nombre ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

</section>

@endsection
