@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

@php
    $colores = ['planificado' => 'secondary', 'en_curso' => 'warning', 'finalizado' => 'success'];
    $etiquetas = ['planificado' => 'Planificado', 'en_curso' => 'En curso', 'finalizado' => 'Finalizado'];
@endphp

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-mortarboard me-2"></i>{{ $proceso->nombre }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('procesos.index') }}">Procesos</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $colores[$proceso->estado] }} fs-6 px-3 py-2">{{ $etiquetas[$proceso->estado] }}</span>
        <a href="{{ route('procesos.edit', $proceso->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
    </div>
</div>

<section class="section mt-3">
<div class="row g-4">

    <div class="col-lg-5">

        {{-- Sesiones --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-calendar3 me-1"></i>Sesiones
                    <span class="badge bg-secondary ms-1">{{ $proceso->sesiones->count() }}</span>
                </span>
                @if($proceso->tipoProceso->numero_sesiones)
                <form action="{{ route('procesos.sesiones.generar', $proceso->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-lightning-charge me-1"></i>Generar sesiones
                    </button>
                </form>
                @endif
            </div>
            <div class="card-body border-bottom">
                <form action="{{ route('procesos.sesiones.store', $proceso->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-3">
                        <input type="number" name="numero_sesion" min="1" class="form-control form-control-sm @error('numero_sesion') is-invalid @enderror" placeholder="#" required>
                        @error('numero_sesion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4">
                        <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Nombre (opcional)">
                    </div>
                    <div class="col-3">
                        <input type="date" name="fecha" class="form-control form-control-sm">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th class="text-center">Asistieron</th>
                                <th class="text-center" style="width:90px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proceso->sesiones as $sesion)
                            <tr>
                                <td class="ps-3">{{ $sesion->numero_sesion }}</td>
                                <td>{{ $sesion->nombre ?? '—' }}</td>
                                <td>{{ $sesion->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-center">{{ $sesion->asistencias->where('asistio', true)->count() }}</td>
                                <td class="text-center">
                                    <a href="{{ route('sesiones-proceso.asistencia', $sesion->id) }}" class="btn btn-outline-primary btn-sm" title="Asistencia"><i class="bi bi-clipboard-check"></i></a>
                                    <form action="{{ route('sesiones-proceso.destroy', $sesion->id) }}" method="POST" class="d-inline form-delete-item">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Aún no hay sesiones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">

        {{-- Participantes --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-people me-1"></i>Participantes
                    <span class="badge bg-secondary ms-1">{{ $proceso->participantes->count() }}</span>
                </span>
                <a href="{{ route('procesos.marcar-terminacion', $proceso->id) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check2-square me-1"></i>Marcar quiénes terminaron
                </a>
            </div>
            <div class="card-body border-bottom">
                <form action="{{ route('procesos.participantes.store', $proceso->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-5">
                        <select name="persona_id" class="form-select form-select-sm select2 @error('persona_id') is-invalid @enderror" required>
                            <option value="">Selecciona una persona…</option>
                            @foreach($personas as $persona)
                                <option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>
                            @endforeach
                        </select>
                        @error('persona_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <select name="red_id" class="form-select form-select-sm">
                            <option value="">Sin red…</option>
                            @foreach($redes as $red)
                                <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="estado_participacion" class="form-select form-select-sm">
                            <option value="en_curso">En curso</option>
                            <option value="terminado">Terminado</option>
                            <option value="incompleto">Incompleto</option>
                            <option value="retirado">Retirado</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Persona</th>
                                <th>Red</th>
                                <th>Estado</th>
                                <th class="text-center" style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proceso->participantes as $participante)
                            <tr>
                                <td class="ps-3">{{ $participante->persona?->nombre_completo }}</td>
                                <td>{{ $participante->red?->nombre ?? '—' }}</td>
                                <td style="max-width:150px">
                                    <form action="{{ route('procesos.participantes.update', $participante->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <select name="estado_participacion" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach(['en_curso'=>'En curso','terminado'=>'Terminado','incompleto'=>'Incompleto','retirado'=>'Retirado'] as $valor => $etiqueta)
                                                <option value="{{ $valor }}" @selected($participante->estado_participacion === $valor)>{{ $etiqueta }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('procesos.participantes.destroy', $participante->id) }}" method="POST" class="form-delete-item">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Quitar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay participantes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</section>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });
    $('.form-delete-item').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Confirmar acción?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, continuar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
