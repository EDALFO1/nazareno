@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-scale me-2"></i>Cuentas por cobrar/pagar</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Cuentas pendientes</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('cuentas_pendientes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nueva
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('cuentas_pendientes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="por_cobrar" @selected(request('tipo') === 'por_cobrar')>Por cobrar</option>
                    <option value="por_pagar" @selected(request('tipo') === 'por_pagar')>Por pagar</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input type="checkbox" name="con_saldo" value="1" class="form-check-input" id="fSaldo" @checked(request('con_saldo'))>
                    <label class="form-check-label small" for="fSaldo">Solo con saldo pendiente</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="{{ route('cuentas_pendientes.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
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
                        <th>Concepto</th>
                        <th>Persona</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th>Vence</th>
                        <th class="text-center" style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $cuenta)
                    <tr>
                        <td class="ps-3">
                            <span class="badge {{ $cuenta->tipo === 'por_cobrar' ? 'bg-info' : 'bg-warning' }}">
                                {{ $cuenta->tipo === 'por_cobrar' ? 'Por cobrar' : 'Por pagar' }}
                            </span>
                        </td>
                        <td>{{ $cuenta->descripcion }}</td>
                        <td>{{ $cuenta->persona?->nombre_completo ?? '—' }}</td>
                        <td class="text-end">${{ number_format($cuenta->monto_total, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold {{ $cuenta->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($cuenta->saldo_pendiente, 0, ',', '.') }}
                        </td>
                        <td>
                            @php
                                $colores = ['pendiente' => 'secondary', 'parcial' => 'info', 'pagada' => 'success', 'vencida' => 'danger'];
                                $etiquetas = ['pendiente' => 'Pendiente', 'parcial' => 'Pago parcial', 'pagada' => 'Pagada', 'vencida' => 'Vencida'];
                            @endphp
                            <span class="badge bg-{{ $colores[$cuenta->estado] }}">{{ $etiquetas[$cuenta->estado] }}</span>
                        </td>
                        <td>{{ $cuenta->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-center">
                            <a href="{{ route('cuentas_pendientes.show', $cuenta->id) }}" class="btn btn-outline-primary btn-sm" title="Ver"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('cuentas_pendientes.edit', $cuenta->id) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('cuentas_pendientes.destroy', $cuenta->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $cuenta->descripcion }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay cuentas pendientes registradas.
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
