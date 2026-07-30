@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Editar movimiento contable</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('movimientos_contables.index') }}">Movimientos contables</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
    <form action="{{ route('movimientos_contables.update', $movimiento->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.movimientos_contables.form')
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar</button>
            <a href="{{ route('movimientos_contables.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</section>

@endsection
