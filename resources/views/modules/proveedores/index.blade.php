@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-truck me-2"></i>Proveedores</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Proveedores</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
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
                        <th>NIT</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th class="text-center">Compras</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $proveedor)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $proveedor->nombre }}</td>
                        <td>{{ $proveedor->nit ?? '—' }}</td>
                        <td>{{ $proveedor->telefono ?? '—' }}</td>
                        <td>{{ $proveedor->correo ?? '—' }}</td>
                        <td class="text-center">{{ $proveedor->movimientos_count }}</td>
                        <td class="text-center">
                            <i class="bi {{ $proveedor->activo ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('proveedores.destroy', $proveedor->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $proveedor->nombre }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay proveedores registrados.
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
