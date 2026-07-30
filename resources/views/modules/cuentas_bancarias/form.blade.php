@php $cuenta = $cuenta ?? null; @endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Cuenta de Ahorros Bancolombia" value="{{ old('nombre', $cuenta->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Banco</label>
        <input type="text" name="banco" class="form-control @error('banco') is-invalid @enderror" value="{{ old('banco', $cuenta->banco ?? '') }}">
        @error('banco') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Número de cuenta</label>
        <input type="text" name="numero_cuenta" class="form-control @error('numero_cuenta') is-invalid @enderror" value="{{ old('numero_cuenta', $cuenta->numero_cuenta ?? '') }}">
        @error('numero_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo de cuenta</label>
        <select name="tipo_cuenta" class="form-select @error('tipo_cuenta') is-invalid @enderror">
            <option value="">Selecciona…</option>
            <option value="ahorros" @selected(old('tipo_cuenta', $cuenta->tipo_cuenta ?? '') === 'ahorros')>Ahorros</option>
            <option value="corriente" @selected(old('tipo_cuenta', $cuenta->tipo_cuenta ?? '') === 'corriente')>Corriente</option>
        </select>
        @error('tipo_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Saldo inicial <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" name="saldo_inicial" class="form-control @error('saldo_inicial') is-invalid @enderror" value="{{ old('saldo_inicial', $cuenta->saldo_inicial ?? 0) }}" required>
        </div>
        <small class="text-muted">El saldo con el que arrancó esta cuenta antes de registrar movimientos aquí.</small>
        @error('saldo_inicial') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <input type="hidden" name="activa" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" name="activa" value="1" class="form-check-input" id="activa" @checked(old('activa', $cuenta->activa ?? true))>
            <label class="form-check-label" for="activa">Activa</label>
        </div>
    </div>
</div>
