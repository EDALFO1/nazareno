@php $puntoConexion = $puntos_conexion ?? null; @endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $puntoConexion->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Red <span class="text-danger">*</span></label>
        <select name="red_id" class="form-select select2 @error('red_id') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            @foreach($redes as $red)
                <option value="{{ $red->id }}" @selected(old('red_id', $puntoConexion->red_id ?? '') == $red->id)>{{ $red->nombre }}</option>
            @endforeach
        </select>
        @error('red_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Líder <span class="text-danger">*</span></label>
        <select name="lider_id" class="form-select select2 @error('lider_id') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            @foreach($personas as $persona)
                <option value="{{ $persona->id }}" @selected(old('lider_id', $puntoConexion->lider_id ?? '') == $persona->id)>{{ $persona->nombre_completo }}</option>
            @endforeach
        </select>
        @error('lider_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Anfitrión</label>
        <select name="anfitrion_persona_id" class="form-select select2 @error('anfitrion_persona_id') is-invalid @enderror">
            <option value="">Sin especificar…</option>
            @foreach($personas as $persona)
                <option value="{{ $persona->id }}" @selected(old('anfitrion_persona_id', $puntoConexion->anfitrion_persona_id ?? '') == $persona->id)>{{ $persona->nombre_completo }}</option>
            @endforeach
        </select>
        @error('anfitrion_persona_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Día</label>
        <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror">
            <option value="">Selecciona…</option>
            @foreach(['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('dia_semana', $puntoConexion->dia_semana ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('dia_semana') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Hora</label>
        <input type="time" name="hora" class="form-control @error('hora') is-invalid @enderror" value="{{ old('hora', $puntoConexion->hora ?? '') }}">
        @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" @checked(old('activo', $puntoConexion->activo ?? true))>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Dirección</label>
        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $puntoConexion->direccion ?? '') }}">
        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });
});
</script>
@endpush
