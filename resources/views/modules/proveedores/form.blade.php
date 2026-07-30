@php $proveedor = $proveedor ?? null; @endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nombre / Razón social <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $proveedor->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">NIT / Documento</label>
        <input type="text" name="nit" class="form-control @error('nit') is-invalid @enderror" value="{{ old('nit', $proveedor->nit ?? '') }}">
        @error('nit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $proveedor->telefono ?? '') }}">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Correo</label>
        <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo', $proveedor->correo ?? '') }}">
        @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Dirección</label>
        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $proveedor->direccion ?? '') }}">
        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Notas</label>
        <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" rows="3">{{ old('notas', $proveedor->notas ?? '') }}</textarea>
        @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <input type="hidden" name="activo" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" @checked(old('activo', $proveedor->activo ?? true))>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
