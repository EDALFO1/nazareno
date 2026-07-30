@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

@php
    $colores = ['pendiente' => 'secondary', 'parcial' => 'info', 'pagada' => 'success', 'vencida' => 'danger'];
    $etiquetas = ['pendiente' => 'Pendiente', 'parcial' => 'Pago parcial', 'pagada' => 'Pagada', 'vencida' => 'Vencida'];
@endphp

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-scale me-2"></i>{{ $cuentas_pendiente->descripcion }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cuentas_pendientes.index') }}">Cuentas pendientes</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $colores[$cuentas_pendiente->estado] }} fs-6 px-3 py-2">{{ $etiquetas[$cuentas_pendiente->estado] }}</span>
        <a href="{{ route('cuentas_pendientes.edit', $cuentas_pendiente->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
    </div>
</div>

<section class="section mt-3">
<div class="row g-4">

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold">Datos</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Tipo</dt>
                    <dd class="col-6">{{ $cuentas_pendiente->tipo === 'por_cobrar' ? 'Por cobrar' : 'Por pagar' }}</dd>
                    <dt class="col-6 text-muted">Categoría</dt>
                    <dd class="col-6">{{ $cuentas_pendiente->categoriaContable?->nombre }}</dd>
                    <dt class="col-6 text-muted">Persona</dt>
                    <dd class="col-6">{{ $cuentas_pendiente->persona?->nombre_completo ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Fecha</dt>
                    <dd class="col-6">{{ $cuentas_pendiente->fecha->format('d/m/Y') }}</dd>
                    <dt class="col-6 text-muted">Vencimiento</dt>
                    <dd class="col-6">{{ $cuentas_pendiente->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd>
                </dl>
                @if($cuentas_pendiente->notas)
                <hr class="my-2">
                <div class="text-muted small mb-1">Notas</div>
                <div class="small">{{ $cuentas_pendiente->notas }}</div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="small text-muted">Total</div>
                        <div class="fw-bold">${{ number_format($cuentas_pendiente->monto_total, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Pagado</div>
                        <div class="fw-bold text-success">${{ number_format($cuentas_pendiente->monto_pagado, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Saldo</div>
                        <div class="fw-bold text-danger">${{ number_format($cuentas_pendiente->saldo_pendiente, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <span class="fw-semibold"><i class="bi bi-cash-stack me-1"></i>Abonos / pagos registrados
                    <span class="badge bg-secondary ms-1">{{ $cuentas_pendiente->movimientos->count() }}</span>
                </span>
            </div>

            @if($cuentas_pendiente->saldo_pendiente > 0)
            <div class="card-body border-bottom">
                <form action="{{ route('cuentas_pendientes.abonos.store', $cuentas_pendiente->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="date" name="fecha" class="form-control form-control-sm @error('fecha') is-invalid @enderror" value="{{ now()->format('Y-m-d') }}" required>
                        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control form-control-sm @error('monto') is-invalid @enderror" placeholder="Monto" required>
                        @error('monto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <select name="metodo_pago" class="form-select form-select-sm">
                            <option value="efectivo">Efectivo</option>
                            <option value="consignacion">Consignación</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="cuenta_bancaria_id" class="form-select form-select-sm">
                            <option value="">Cuenta bancaria…</option>
                            @foreach($cuentasBancarias as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Registrar</button>
                    </div>
                </form>
            </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Fecha</th>
                                <th class="text-end">Monto</th>
                                <th>Método</th>
                                <th>Cuenta bancaria</th>
                                <th>Concepto</th>
                                <th class="text-center" style="width:90px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $metodos = ['efectivo'=>'Efectivo','consignacion'=>'Consignación','transferencia'=>'Transferencia','cheque'=>'Cheque']; @endphp
                            @forelse($cuentas_pendiente->movimientos as $movimiento)
                            <tr>
                                <td class="ps-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="text-end">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $metodos[$movimiento->metodo_pago] ?? $movimiento->metodo_pago }}</span></td>
                                <td>{{ $movimiento->cuentaBancaria?->nombre ?? '—' }}</td>
                                <td>{{ $movimiento->descripcion ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('movimientos_contables.edit', $movimiento->id) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('movimientos_contables.destroy', $movimiento->id) }}" method="POST" class="d-inline form-delete-item">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Aún no hay abonos registrados.</td></tr>
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
    $('.form-delete-item').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Eliminar abono?',
            text: 'Esta acción no se puede deshacer.',
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
