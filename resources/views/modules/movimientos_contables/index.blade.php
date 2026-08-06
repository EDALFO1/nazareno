@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Movimientos contables</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Movimientos contables</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('movimientos_contables.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

@if($diezmosDelMes > 0)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                <i class="bi bi-percent text-warning fs-5"></i>
            </div>
            <div>
                <div class="fw-semibold">Diezmo de diezmos — {{ ucfirst($mesActual->translatedFormat('F Y')) }}</div>
                <div class="small text-muted">
                    Diezmos + Ofrenda general recibidos: <strong>${{ number_format($diezmosDelMes, 0, ',', '.') }}</strong>
                    — 15% a girar a la iglesia principal: <strong>${{ number_format($diezmosDelMes * 0.15, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        @if($cuentaDiezmoDelMes)
        <a href="{{ route('cuentas_pendientes.show', $cuentaDiezmoDelMes->id) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-eye me-1"></i>Ver cuenta pendiente
            @if($cuentaDiezmoDelMes->saldo_pendiente > 0)
                <span class="badge bg-warning text-dark ms-1">Saldo ${{ number_format($cuentaDiezmoDelMes->saldo_pendiente, 0, ',', '.') }}</span>
            @else
                <span class="badge bg-success ms-1">Pagada</span>
            @endif
        </a>
        @endif
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos_contables.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="ingreso" @selected(request('tipo') === 'ingreso')>Ingreso</option>
                    <option value="egreso" @selected(request('tipo') === 'egreso')>Egreso</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Categoría</label>
                <select name="categoria_contable_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(request('categoria_contable_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Red</label>
                <select name="red_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($redes as $red)
                        <option value="{{ $red->id }}" @selected(request('red_id') == $red->id)>{{ $red->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                        <th>Persona / Proveedor</th>
                        <th>Red</th>
                        <th>Método</th>
                        <th class="text-center">Comprobante</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $metodos = ['efectivo'=>'Efectivo','consignacion'=>'Consignación','transferencia'=>'Transferencia','cheque'=>'Cheque']; @endphp
                    @forelse($movimientos as $movimiento)
                    <tr>
                        <td class="ps-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $movimiento->tipo === 'ingreso' ? 'bg-success' : 'bg-danger' }}">
                                {{ $movimiento->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $movimiento->categoriaContable?->nombre }}</span></td>
                        <td class="text-end fw-bold">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                        <td>
                            @if($movimiento->proveedor)
                                <i class="bi bi-truck text-muted me-1" title="Proveedor"></i>{{ $movimiento->proveedor->nombre }}
                            @else
                                {{ $movimiento->persona?->nombre_completo ?? '—' }}
                            @endif
                        </td>
                        <td>{{ $movimiento->red?->nombre ?? '—' }}</td>
                        <td>{{ $metodos[$movimiento->metodo_pago] ?? $movimiento->metodo_pago }}</td>
                        <td class="text-center">
                            @if($movimiento->comprobante)
                                <a href="{{ route('movimientos_contables.comprobante', $movimiento->id) }}" target="_blank" title="Ver comprobante">
                                    <i class="bi bi-file-earmark-check-fill text-success"></i>
                                </a>
                            @else
                                <i class="bi bi-dash text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('movimientos_contables.edit', $movimiento->id) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('movimientos_contables.destroy', $movimiento->id) }}" method="POST" class="d-inline form-delete" data-nombre="este movimiento">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay movimientos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $movimientos->links() }}
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
