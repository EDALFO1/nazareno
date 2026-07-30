@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-person-gear me-2"></i>Editar persona</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('personas.index') }}">Personas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('personas.show', $persona->id) }}">{{ $persona->nombre_completo }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
    <form action="{{ route('personas.update', $persona->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('modules.personas.form', ['mostrarAutorizacion' => $exigirAutorizacion])
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar</button>
            <a href="{{ route('personas.show', $persona->id) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</section>

@endsection
