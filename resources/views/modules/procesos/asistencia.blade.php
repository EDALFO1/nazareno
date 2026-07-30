@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Asistencia</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procesos.index') }}">Procesos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procesos.show', $sesion_proceso->proceso_id) }}">{{ $sesion_proceso->proceso->nombre }}</a></li>
            <li class="breadcrumb-item active">Asistencia sesión {{ $sesion_proceso->numero_sesion }}</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold">Personas que asistieron</div>
    <div class="card-body">
        <form action="{{ route('sesiones-proceso.asistencia.store', $sesion_proceso->id) }}" method="POST">
            @csrf
            <div class="row g-2">
                @forelse($sesion_proceso->proceso->participantes as $participante)
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox" name="presentes[]" value="{{ $participante->persona_id }}" class="form-check-input" id="p-{{ $participante->persona_id }}" @checked(in_array($participante->persona_id, $presentes))>
                        <label class="form-check-label" for="p-{{ $participante->persona_id }}">{{ $participante->persona?->nombre_completo }}</label>
                    </div>
                </div>
                @empty
                <div class="col-12 text-muted text-center py-4">Este proceso aún no tiene participantes.</div>
                @endforelse
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar asistencia</button>
                <a href="{{ route('procesos.show', $sesion_proceso->proceso_id) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>

@endsection
