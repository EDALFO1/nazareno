@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Certificado de donante</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Certificado de donante</li>
        </ol>
    </nav>
</div>

<section class="section mt-3 print-area">

<div class="card border-0 shadow-sm mb-3 print-hide">
    <div class="card-body">
        <form method="GET" action="{{ route('certificado-donante.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">Persona</label>
                <select name="persona_id" class="form-select select2" onchange="this.form.submit()">
                    <option value="">Selecciona una persona…</option>
                    @foreach($personas as $persona)
                        <option value="{{ $persona->id }}" @selected($personaId == $persona->id)>{{ $persona->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Año</label>
                <select name="anio" class="form-select" onchange="this.form.submit()">
                    @foreach($anios as $a)
                        <option value="{{ $a }}" @selected($anio == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            @if($certificado)
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir</button>
            </div>
            @endif
        </form>
    </div>
</div>

@if(! $certificado)
<div class="card border-0 shadow-sm">
    <div class="card-body text-muted">
        Selecciona una persona y un año arriba para ver el total donado y generar su certificado.
    </div>
</div>
@else
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h2 class="fs-4 fw-bold mb-1">{{ $certificado['persona']->nombre_completo }}</h2>
        <p class="text-muted mb-0">Certificado de donaciones — año {{ $anio }}</p>
    </div>
</div>

<div class="row g-3 mb-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Total donado en efectivo/consignación</div>
                <div class="fs-3 fw-bold">${{ number_format($certificado['totalEfectivo'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted">Total donado en especie (activos)</div>
                <div class="fs-3 fw-bold">${{ number_format($certificado['totalActivos'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Detalle de ingresos en efectivo</div>
    <div class="card-body p-0">
        @if($certificado['movimientos']->isEmpty())
            <p class="text-muted text-center py-4 mb-0">No hay donaciones en efectivo registradas para esta persona en {{ $anio }}.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-3">Fecha</th><th>Categoría</th><th>Método</th><th>Monto</th></tr>
                </thead>
                <tbody>
                    @foreach($certificado['movimientos'] as $movimiento)
                    <tr>
                        <td class="ps-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td>{{ $movimiento->categoriaContable?->nombre }}</td>
                        <td class="text-capitalize">{{ $movimiento->metodo_pago }}</td>
                        <td>${{ number_format((float) $movimiento->monto, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent fw-semibold">Detalle de donaciones en especie</div>
    <div class="card-body p-0">
        @if($certificado['donacionesActivos']->isEmpty())
            <p class="text-muted text-center py-4 mb-0">No hay donaciones en especie registradas para esta persona en {{ $anio }}.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-3">Fecha</th><th>Descripción</th><th>Valor estimado</th></tr>
                </thead>
                <tbody>
                    @foreach($certificado['donacionesActivos'] as $donacion)
                    <tr>
                        <td class="ps-3">{{ $donacion->fecha->format('d/m/Y') }}</td>
                        <td>{{ $donacion->descripcion }}</td>
                        <td>{{ $donacion->valor_estimado ? '$'.number_format((float) $donacion->valor_estimado, 0, ',', '.') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

</section>

@push('styles')
<style>
@media print {
    .print-hide, #header, #sidebar, #footer { display: none !important; }
    #main { margin: 0 !important; padding: 0 !important; }
}
</style>
@endpush

@push('scripts')
<script>
$(function () { $('.select2').select2({ theme: 'default', width: '100%' }); });
</script>
@endpush

@endsection
