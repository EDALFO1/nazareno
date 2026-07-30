@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-people me-2"></i>Usuarios</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Usuarios</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="px-3 pt-3 text-muted small">
            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} registros
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Persona vinculada</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rolEtiquetas = ['super_admin' => 'Admin Principal', 'admin_general' => 'Admin General', 'lider_red' => 'Líder de Red'];
                    @endphp
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            @if($usuario->rol)
                                <span class="badge bg-light text-dark border">{{ $rolEtiquetas[$usuario->rol->nombre] ?? $usuario->rol->nombre }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $usuario->persona?->nombre_completo ?? '—' }}</td>
                        <td class="text-center">
                            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($usuario->id !== auth()->id())
                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $usuario->name }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay usuarios registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $usuarios->links() }}
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
