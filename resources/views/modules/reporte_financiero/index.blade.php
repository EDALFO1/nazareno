@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-graph-up me-2"></i>Reporte financiero</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Reporte financiero</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('reportes.exportar', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-download me-1"></i>Exportar a Excel
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="desde" class="form-control" value="{{ $desde }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-1">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Saldo actual (caja/bancos)</div>
                <div class="fs-3 fw-bold {{ $reporte['saldoActual'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($reporte['saldoActual'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Por cobrar (nos deben)</div>
                <div class="fs-3 fw-bold text-info">${{ number_format($reporte['totalPorCobrar'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Por pagar (debemos)</div>
                <div class="fs-3 fw-bold text-warning">${{ number_format($reporte['totalPorPagar'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-1">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Ingresos del periodo</div>
                <div class="fs-4 fw-bold text-success">${{ number_format($reporte['totalIngresos'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Egresos del periodo</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($reporte['totalEgresos'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @php $balancePeriodo = $reporte['totalIngresos'] - $reporte['totalEgresos']; @endphp
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Balance del periodo</div>
                <div class="fs-4 fw-bold {{ $balancePeriodo >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($balancePeriodo, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">Ingresos por categoría</div>
            <div class="card-body p-0">
                @if($reporte['ingresosPorCategoria']->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">Sin ingresos en el periodo seleccionado.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-3">Categoría</th><th>Movimientos</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($reporte['ingresosPorCategoria'] as $fila)
                            <tr>
                                <td class="ps-3">{{ $fila['categoria'] }}</td>
                                <td>{{ $fila['cantidad'] }}</td>
                                <td class="fw-semibold">${{ number_format($fila['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">Egresos por categoría</div>
            <div class="card-body p-0">
                @if($reporte['egresosPorCategoria']->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">Sin egresos en el periodo seleccionado.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-3">Categoría</th><th>Movimientos</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($reporte['egresosPorCategoria'] as $fila)
                            <tr>
                                <td class="ps-3">{{ $fila['categoria'] }}</td>
                                <td>{{ $fila['cantidad'] }}</td>
                                <td class="fw-semibold">${{ number_format($fila['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Detalle de ingresos</div>
    <div class="card-body p-0">
        @if($reporte['movimientosIngreso']->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Sin ingresos en el periodo seleccionado.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Fecha</th><th>Categoría</th><th>Persona</th><th>Método</th><th>Monto</th></tr></thead>
                <tbody>
                    @foreach($reporte['movimientosIngreso'] as $movimiento)
                    <tr>
                        <td class="ps-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td>{{ $movimiento->categoriaContable?->nombre }}</td>
                        <td>{{ $movimiento->persona?->nombre_completo ?? '—' }}</td>
                        <td class="text-capitalize">{{ $movimiento->metodo_pago }}</td>
                        <td class="fw-semibold text-success">${{ number_format((float) $movimiento->monto, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Detalle de egresos</div>
    <div class="card-body p-0">
        @if($reporte['movimientosEgreso']->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Sin egresos en el periodo seleccionado.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Fecha</th><th>Categoría</th><th>Concepto</th><th>Método</th><th>Monto</th></tr></thead>
                <tbody>
                    @foreach($reporte['movimientosEgreso'] as $movimiento)
                    <tr>
                        <td class="ps-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td>{{ $movimiento->categoriaContable?->nombre }}</td>
                        <td>{{ $movimiento->descripcion ?? '—' }}</td>
                        <td class="text-capitalize">{{ $movimiento->metodo_pago }}</td>
                        <td class="fw-semibold text-danger">${{ number_format((float) $movimiento->monto, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

</section>

@endsection
