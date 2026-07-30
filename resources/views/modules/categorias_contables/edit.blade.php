@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-tag me-2"></i>Editar categoría contable</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias_contables.index') }}">Categorías contables</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('categorias_contables.update', $categoria->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('modules.categorias_contables.form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Guardar</button>
                <a href="{{ route('categorias_contables.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>

@endsection
