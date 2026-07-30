@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Puntos de conexión</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Puntos de conexión</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('puntos_conexion.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Red</th>
                        <th>Líder</th>
                        <th>Anfitrión</th>
                        <th>Día</th>
                        <th>Hora</th>
                        <th class="text-center">Miembros</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center" style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($puntos as $punto)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $punto->nombre }}</td>
                        <td>{{ $punto->red?->nombre ?? '—' }}</td>
                        <td>{{ $punto->lider?->nombre_completo ?? '—' }}</td>
                        <td>{{ $punto->anfitrion?->nombre_completo ?? '—' }}</td>
                        <td>{{ $punto->dia_semana ? ucfirst($punto->dia_semana) : '—' }}</td>
                        <td>{{ $punto->hora ? \Illuminate\Support\Carbon::parse($punto->hora)->format('h:i A') : '—' }}</td>
                        <td class="text-center">{{ $punto->miembros_count }}</td>
                        <td class="text-center">
                            <i class="bi {{ $punto->activo ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('puntos_conexion.show', $punto->id) }}" class="btn btn-outline-primary btn-sm" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('puntos_conexion.edit', $punto->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('puntos_conexion.destroy', $punto->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $punto->nombre }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay puntos de conexión registrados.
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
