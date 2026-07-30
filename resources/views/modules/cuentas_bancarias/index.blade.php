@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-bank me-2"></i>Cuentas bancarias</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Cuentas bancarias</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('cuentas_bancarias.create') }}" class="btn btn-primary">
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
                        <th class="ps-3">Nombre</th>
                        <th>Banco</th>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th class="text-end">Saldo actual</th>
                        <th class="text-center">Activa</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $cuenta)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $cuenta->nombre }}</td>
                        <td>{{ $cuenta->banco ?? '—' }}</td>
                        <td>{{ $cuenta->numero_cuenta ?? '—' }}</td>
                        <td>
                            @if($cuenta->tipo_cuenta)
                                <span class="badge bg-light text-dark border">{{ ucfirst($cuenta->tipo_cuenta) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end fw-bold">${{ number_format($cuenta->saldo_actual, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <i class="bi {{ $cuenta->activa ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('cuentas_bancarias.edit', $cuenta->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('cuentas_bancarias.destroy', $cuenta->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $cuenta->nombre }}">
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
                            No hay cuentas registradas.
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
