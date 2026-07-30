@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Procesos de formación</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Procesos</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('procesos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('procesos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Tipo de proceso</label>
                <select name="tipo_proceso_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($tiposProceso as $tipo)
                        <option value="{{ $tipo->id }}" @selected(request('tipo_proceso_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['planificado'=>'Planificado','en_curso'=>'En curso','finalizado'=>'Finalizado'] as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="{{ route('procesos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tipo</th>
                        <th>Edición</th>
                        <th>Fecha de inicio</th>
                        <th>Estado</th>
                        <th class="text-center">Participantes</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procesos as $proceso)
                    <tr>
                        <td class="ps-3"><span class="badge bg-light text-dark border">{{ $proceso->tipoProceso->nombre }}</span></td>
                        <td>{{ $proceso->nombre }}</td>
                        <td>{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @php
                                $colores = ['planificado' => 'secondary', 'en_curso' => 'warning', 'finalizado' => 'success'];
                                $etiquetas = ['planificado' => 'Planificado', 'en_curso' => 'En curso', 'finalizado' => 'Finalizado'];
                            @endphp
                            <span class="badge bg-{{ $colores[$proceso->estado] }}">{{ $etiquetas[$proceso->estado] }}</span>
                        </td>
                        <td class="text-center">{{ $proceso->participantes_count }}</td>
                        <td class="text-center">
                            <a href="{{ route('procesos.show', $proceso->id) }}" class="btn btn-outline-primary btn-sm" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('procesos.edit', $proceso->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('procesos.destroy', $proceso->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $proceso->nombre }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay procesos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</section>

@push('scripts')
<script>
$(function () {
    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).data('nombre') || 'este registro';
        Swal.fire({
            title: '¿Eliminar registro?',
            text: `«${nombre}» será eliminado permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
