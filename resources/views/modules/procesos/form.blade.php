@php $proceso = $proceso ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo de proceso <span class="text-danger">*</span></label>
        <select name="tipo_proceso_id" class="form-select @error('tipo_proceso_id') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            @foreach($tiposProceso as $tipo)
                <option value="{{ $tipo->id }}" @selected(old('tipo_proceso_id', $proceso->tipo_proceso_id ?? '') == $tipo->id)>{{ $tipo->nombre }}</option>
            @endforeach
        </select>
        @error('tipo_proceso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-5">
        <label class="form-label fw-semibold">Nombre de la edición <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Encuentro Hombres Julio 2026" value="{{ old('nombre', $proceso->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Fecha de inicio</label>
        <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror" value="{{ old('fecha_inicio', isset($proceso->fecha_inicio) ? $proceso->fecha_inicio->format('Y-m-d') : '') }}">
        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
            @foreach(['planificado'=>'Planificado','en_curso'=>'En curso','finalizado'=>'Finalizado'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado', $proceso->estado ?? 'planificado') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if(! $proceso && isset($procesosAnteriores) && $procesosAnteriores->isNotEmpty())
    <div class="col-md-8">
        <label class="form-label fw-semibold">Cargar participantes que terminaron un proceso anterior</label>
        <select name="cargar_desde_proceso_id" class="form-select select2 @error('cargar_desde_proceso_id') is-invalid @enderror">
            <option value="">No cargar…</option>
            @foreach($procesosAnteriores as $anterior)
                <option value="{{ $anterior['id'] }}">{{ $anterior['label'] }}</option>
            @endforeach
        </select>
        <small class="text-muted">Trae automáticamente, como participantes de esta edición, a quienes terminaron la edición que elijas.</small>
        @error('cargar_desde_proceso_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
    @endif
</div>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });
});
</script>
@endpush
