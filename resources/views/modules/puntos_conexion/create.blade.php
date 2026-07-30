@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Crear punto de conexión</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('puntos_conexion.index') }}">Puntos de conexión</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('puntos_conexion.store') }}" method="POST">
            @csrf
            @include('modules.puntos_conexion.form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar</button>
                <a href="{{ route('puntos_conexion.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>

@endsection
