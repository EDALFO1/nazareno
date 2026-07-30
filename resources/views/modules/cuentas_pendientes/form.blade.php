@php
    $cuenta = $cuenta ?? null;
    $categoriasIngreso = $categoriasIngreso->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre]);
    $categoriasEgreso = $categoriasEgreso->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre]);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            <option value="por_cobrar" @selected(old('tipo', $cuenta->tipo ?? '') === 'por_cobrar')>Por cobrar (nos deben)</option>
            <option value="por_pagar" @selected(old('tipo', $cuenta->tipo ?? '') === 'por_pagar')>Por pagar (debemos)</option>
        </select>
        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold" id="label-categoria">Categoría <span class="text-danger">*</span></label>
        <select name="categoria_contable_id" id="categoria_contable_id" class="form-select @error('categoria_contable_id') is-invalid @enderror" required>
            <option value="">Selecciona un tipo primero…</option>
        </select>
        @error('categoria_contable_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold" id="label-persona">Persona</label>
        <select name="persona_id" class="form-select select2 @error('persona_id') is-invalid @enderror">
            <option value="">Sin especificar…</option>
            @foreach($personas as $persona)
                <option value="{{ $persona->id }}" @selected(old('persona_id', $cuenta->persona_id ?? '') == $persona->id)>{{ $persona->nombre_completo }}</option>
            @endforeach
        </select>
        <small class="text-muted">Opcional. Solo aplica si ya está registrado en el sistema como Persona.</small>
        @error('persona_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Concepto <span class="text-danger">*</span></label>
        <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Ej. Compromiso ofrenda construcción, Factura reparación techo" value="{{ old('descripcion', $cuenta->descripcion ?? '') }}" required>
        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Monto total <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0.01" name="monto_total" class="form-control @error('monto_total') is-invalid @enderror" value="{{ old('monto_total', $cuenta->monto_total ?? '') }}" required>
        </div>
        @error('monto_total') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
        <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', isset($cuenta->fecha) ? $cuenta->fecha->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha de vencimiento</label>
        <input type="date" name="fecha_vencimiento" class="form-control @error('fecha_vencimiento') is-invalid @enderror" value="{{ old('fecha_vencimiento', isset($cuenta->fecha_vencimiento) ? $cuenta->fecha_vencimiento->format('Y-m-d') : '') }}">
        @error('fecha_vencimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Notas</label>
        <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" rows="2">{{ old('notas', $cuenta->notas ?? '') }}</textarea>
        @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });

    const categoriasIngreso = @json($categoriasIngreso);
    const categoriasEgreso = @json($categoriasEgreso);
    const categoriaSeleccionada = "{{ old('categoria_contable_id', $cuenta->categoria_contable_id ?? '') }}";

    function actualizarLabels() {
        const tipo = $('#tipo').val();
        $('#label-persona').text(tipo === 'por_pagar' ? 'Proveedor / a quién le debemos' : 'Persona que debe');
    }

    function poblarCategorias() {
        const tipo = $('#tipo').val();
        const opciones = tipo === 'por_cobrar' ? categoriasIngreso : (tipo === 'por_pagar' ? categoriasEgreso : []);
        const $select = $('#categoria_contable_id');
        $select.empty().append('<option value="">Selecciona…</option>');
        opciones.forEach(function (c) {
            const selected = String(c.id) === categoriaSeleccionada ? 'selected' : '';
            $select.append(`<option value="${c.id}" ${selected}>${c.nombre}</option>`);
        });
        actualizarLabels();
    }

    $('#tipo').on('change', poblarCategorias);
    poblarCategorias();
});
</script>
@endpush
