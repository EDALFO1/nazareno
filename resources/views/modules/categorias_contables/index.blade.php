@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-tag me-2"></i>Categorías contables</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Categorías contables</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('categorias_contables.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nueva
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tipo</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Movimientos</th>
                        <th class="text-center">Activa</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                    <tr>
                        <td class="ps-3">
                            <span class="badge {{ $categoria->tipo === 'ingreso' ? 'bg-success' : 'bg-danger' }}">
                                {{ $categoria->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                            </span>
                        </td>
                        <td>{{ $categoria->nombre }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($categoria->descripcion, 50) }}</td>
                        <td class="text-center">{{ $categoria->movimientos_count }}</td>
                        <td class="text-center">
                            <i class="bi {{ $categoria->activo ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('categorias_contables.edit', $categoria->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('categorias_contables.destroy', $categoria->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $categoria->nombre }}">
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
                            No hay categorías registradas.
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
