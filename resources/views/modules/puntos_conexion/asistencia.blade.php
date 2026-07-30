@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Asistencia</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('puntos_conexion.index') }}">Puntos de conexión</a></li>
            <li class="breadcrumb-item"><a href="{{ route('puntos_conexion.show', $sesion_punto_conexion->punto_conexion_id) }}">{{ $sesion_punto_conexion->puntoConexion->nombre }}</a></li>
            <li class="breadcrumb-item active">Asistencia {{ $sesion_punto_conexion->fecha->format('d/m/Y') }}</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold">Personas que asistieron</div>
    <div class="card-body">
        <form action="{{ route('sesiones-punto-conexion.asistencia.store', $sesion_punto_conexion->id) }}" method="POST">
            @csrf
            <div class="row g-2">
                @forelse($sesion_punto_conexion->puntoConexion->miembros as $miembro)
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox" name="presentes[]" value="{{ $miembro->id }}" class="form-check-input" id="miembro-{{ $miembro->id }}" @checked(in_array($miembro->id, $presentes))>
                        <label class="form-check-label" for="miembro-{{ $miembro->id }}">{{ $miembro->nombre_completo }}</label>
                    </div>
                </div>
                @empty
                <div class="col-12 text-muted text-center py-4">Este punto de conexión aún no tiene miembros.</div>
                @endforelse
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar asistencia</button>
                <a href="{{ route('puntos_conexion.show', $sesion_punto_conexion->punto_conexion_id) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>

@endsection
