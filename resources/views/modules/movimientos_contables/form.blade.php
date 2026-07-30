@php
    $movimiento = $movimiento ?? null;
    $categoriasIngreso = $categorias->where('tipo', 'ingreso')->values()->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre]);
    $categoriasEgreso = $categorias->where('tipo', 'egreso')->values()->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre]);
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Movimiento</div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
            <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                <option value="">Selecciona…</option>
                <option value="ingreso" @selected(old('tipo', $movimiento->tipo ?? '') === 'ingreso')>Ingreso</option>
                <option value="egreso" @selected(old('tipo', $movimiento->tipo ?? '') === 'egreso')>Egreso</option>
            </select>
            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
            <select name="categoria_contable_id" id="categoria_contable_id" class="form-select @error('categoria_contable_id') is-invalid @enderror" required>
                <option value="">Selecciona un tipo primero…</option>
            </select>
            @error('categoria_contable_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', isset($movimiento->fecha) ? $movimiento->fecha->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $movimiento->monto ?? '') }}" required>
            </div>
            @error('monto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Método de pago <span class="text-danger">*</span></label>
            <select name="metodo_pago" class="form-select @error('metodo_pago') is-invalid @enderror" required>
                @foreach(['efectivo'=>'Efectivo','consignacion'=>'Consignación','transferencia'=>'Transferencia','cheque'=>'Cheque'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('metodo_pago', $movimiento->metodo_pago ?? 'efectivo') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('metodo_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Número de referencia</label>
            <input type="text" name="referencia" class="form-control @error('referencia') is-invalid @enderror" value="{{ old('referencia', $movimiento->referencia ?? '') }}">
            @error('referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Relacionado con</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Persona</label>
            <select name="persona_id" class="form-select select2 @error('persona_id') is-invalid @enderror">
                <option value="">Sin especificar…</option>
                @foreach($personas as $persona)
                    <option value="{{ $persona->id }}" @selected(old('persona_id', $movimiento->persona_id ?? '') == $persona->id)>{{ $persona->nombre_completo }}</option>
                @endforeach
            </select>
            <small class="text-muted">Opcional. Déjalo vacío para una ofrenda general sin donante específico.</small>
            @error('persona_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Proveedor</label>
            <select name="proveedor_id" class="form-select select2 @error('proveedor_id') is-invalid @enderror">
                <option value="">Sin especificar…</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $movimiento->proveedor_id ?? '') == $proveedor->id)>{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
            <small class="text-muted">Para egresos: a quién se le compró o pagó.</small>
            @error('proveedor_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Red</label>
            <select name="red_id" class="form-select select2 @error('red_id') is-invalid @enderror">
                <option value="">Sin red…</option>
                @foreach($redes as $red)
                    <option value="{{ $red->id }}" @selected(old('red_id', $movimiento->red_id ?? '') == $red->id)>{{ $red->nombre }}</option>
                @endforeach
            </select>
            @error('red_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Punto de conexión</label>
            <select name="punto_conexion_id" class="form-select select2 @error('punto_conexion_id') is-invalid @enderror">
                <option value="">Sin especificar…</option>
                @foreach($puntosConexion as $punto)
                    <option value="{{ $punto->id }}" @selected(old('punto_conexion_id', $movimiento->punto_conexion_id ?? '') == $punto->id)>{{ $punto->nombre }}</option>
                @endforeach
            </select>
            @error('punto_conexion_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Cuenta bancaria</label>
            <select name="cuenta_bancaria_id" class="form-select select2 @error('cuenta_bancaria_id') is-invalid @enderror">
                <option value="">Sin especificar…</option>
                @foreach($cuentasBancarias as $cb)
                    <option value="{{ $cb->id }}" @selected(old('cuenta_bancaria_id', $movimiento->cuenta_bancaria_id ?? '') == $cb->id)>{{ $cb->nombre }}</option>
                @endforeach
            </select>
            <small class="text-muted">Opcional. Solo si este movimiento entró/salió de una cuenta bancaria específica.</small>
            @error('cuenta_bancaria_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Detalle</div>
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Concepto</label>
            <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="2">{{ old('descripcion', $movimiento->descripcion ?? '') }}</textarea>
            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Comprobante (recibo, voucher, factura)</label>
            <input type="file" name="comprobante" accept="image/*" class="form-control @error('comprobante') is-invalid @enderror">
            @if(isset($movimiento) && $movimiento->comprobante)
                <small class="text-muted d-block mt-1">
                    Ya tiene un comprobante cargado —
                    <a href="{{ route('movimientos_contables.comprobante', $movimiento->id) }}" target="_blank">verlo</a>.
                    Sube uno nuevo para reemplazarlo.
                </small>
            @endif
            @error('comprobante') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });

    const categoriasIngreso = @json($categoriasIngreso);
    const categoriasEgreso = @json($categoriasEgreso);
    const categoriaSeleccionada = "{{ old('categoria_contable_id', $movimiento->categoria_contable_id ?? '') }}";

    function poblarCategorias() {
        const tipo = $('#tipo').val();
        const opciones = tipo === 'ingreso' ? categoriasIngreso : (tipo === 'egreso' ? categoriasEgreso : []);
        const $select = $('#categoria_contable_id');
        $select.empty().append('<option value="">Selecciona…</option>');
        opciones.forEach(function (c) {
            const selected = String(c.id) === categoriaSeleccionada ? 'selected' : '';
            $select.append(`<option value="${c.id}" ${selected}>${c.nombre}</option>`);
        });
    }

    $('#tipo').on('change', poblarCategorias);
    poblarCategorias();
});
</script>
@endpush
