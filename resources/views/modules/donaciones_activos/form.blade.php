@php $donacion = $donacion ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Donante</label>
        <select name="persona_id" class="form-select select2 @error('persona_id') is-invalid @enderror">
            <option value="">Sin especificar…</option>
            @foreach($personas as $persona)
                <option value="{{ $persona->id }}" @selected(old('persona_id', $donacion->persona_id ?? '') == $persona->id)>{{ $persona->nombre_completo }}</option>
            @endforeach
        </select>
        <small class="text-muted">Opcional, si se conoce quién la donó.</small>
        @error('persona_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
        <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', isset($donacion->fecha) ? $donacion->fecha->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Valor estimado</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" name="valor_estimado" class="form-control @error('valor_estimado') is-invalid @enderror" value="{{ old('valor_estimado', $donacion->valor_estimado ?? '') }}">
        </div>
        @error('valor_estimado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Descripción del activo <span class="text-danger">*</span></label>
        <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Ej. Escritorio de oficina, impresora HP, guitarra acústica" value="{{ old('descripcion', $donacion->descripcion ?? '') }}" required>
        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Ubicación / asignado a</label>
        <input type="text" name="ubicacion_asignada" class="form-control @error('ubicacion_asignada') is-invalid @enderror" placeholder="Ej. Oficina pastoral, salón de niños" value="{{ old('ubicacion_asignada', $donacion->ubicacion_asignada ?? '') }}">
        @error('ubicacion_asignada') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Notas</label>
        <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" rows="3">{{ old('notas', $donacion->notas ?? '') }}</textarea>
        @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%', placeholder: 'Sin especificar…' });
});
</script>
@endpush
