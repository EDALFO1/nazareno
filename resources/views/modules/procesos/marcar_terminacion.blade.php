@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-check2-square me-2"></i>Marcar quiénes terminaron</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procesos.index') }}">Procesos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procesos.show', $proceso->id) }}">{{ $proceso->nombre }}</a></li>
            <li class="breadcrumb-item active">Marcar terminación</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold">
        Personas que terminaron el proceso
        <small class="text-muted d-block fw-normal mt-1">
            Quien quede sin marcar aquí quedará como "Incompleto" — podrá continuar el proceso más adelante. Los que ya están "Retirado" no se tocan.
        </small>
    </div>
    <div class="card-body">
        <form action="{{ route('procesos.marcar-terminacion.store', $proceso->id) }}" method="POST">
            @csrf
            <div class="row g-2">
                @forelse($proceso->participantes->where('estado_participacion', '!=', 'retirado') as $participante)
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox" name="terminaron[]" value="{{ $participante->persona_id }}" class="form-check-input" id="t-{{ $participante->persona_id }}" @checked(in_array($participante->persona_id, $terminados))>
                        <label class="form-check-label" for="t-{{ $participante->persona_id }}">{{ $participante->persona?->nombre_completo }}</label>
                    </div>
                </div>
                @empty
                <div class="col-12 text-muted text-center py-4">No hay participantes elegibles.</div>
                @endforelse
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar</button>
                <a href="{{ route('procesos.show', $proceso->id) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>

@endsection
