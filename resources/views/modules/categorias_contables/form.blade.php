@php $categoria = $categoria ?? null; @endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            <option value="ingreso" @selected(old('tipo', $categoria->tipo ?? '') === 'ingreso')>Ingreso</option>
            <option value="egreso" @selected(old('tipo', $categoria->tipo ?? '') === 'egreso')>Egreso</option>
        </select>
        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-5">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $categoria->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Descripción</label>
        <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $categoria->descripcion ?? '') }}">
        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <input type="hidden" name="activo" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" @checked(old('activo', $categoria->activo ?? true))>
            <label class="form-check-label" for="activo">Activa</label>
        </div>
        <small class="text-muted">Las categorías inactivas ya no aparecen para elegir en nuevos movimientos.</small>
    </div>
</div>
