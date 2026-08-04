@php
    $persona = $persona ?? null;
    $mostrarAutorizacion = $mostrarAutorizacion ?? true;
    $tiposDocumento = \App\Models\Persona::TIPOS_DOCUMENTO;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Datos personales</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
            <input type="text" name="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres', $persona->nombres ?? '') }}" required>
            @error('nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
            <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $persona->apellidos ?? '') }}" required>
            @error('apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de documento</label>
            <select name="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror">
                <option value="">Selecciona…</option>
                @foreach($tiposDocumento as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('tipo_documento', $persona->tipo_documento ?? '') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('tipo_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Número de documento</label>
            <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" value="{{ old('numero_documento', $persona->numero_documento ?? '') }}">
            @error('numero_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Género</label>
            <select name="genero" class="form-select @error('genero') is-invalid @enderror">
                <option value="">Selecciona…</option>
                <option value="masculino" @selected(old('genero', $persona->genero ?? '') === 'masculino')>Masculino</option>
                <option value="femenino" @selected(old('genero', $persona->genero ?? '') === 'femenino')>Femenino</option>
            </select>
            @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Teléfono</label>
            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $persona->telefono ?? '') }}">
            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Correo</label>
            <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo', $persona->correo ?? '') }}">
            @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', isset($persona->fecha_nacimiento) ? $persona->fecha_nacimiento->format('Y-m-d') : '') }}">
            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Dirección</label>
            <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $persona->direccion ?? '') }}">
            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Fecha de primera visita</label>
            <input type="date" name="fecha_primera_visita" class="form-control @error('fecha_primera_visita') is-invalid @enderror" value="{{ old('fecha_primera_visita', isset($persona->fecha_primera_visita) ? $persona->fecha_primera_visita->format('Y-m-d') : '') }}">
            @error('fecha_primera_visita') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Petición de oración</label>
            <textarea name="peticion_oracion" class="form-control @error('peticion_oracion') is-invalid @enderror" rows="2">{{ old('peticion_oracion', $persona->peticion_oracion ?? '') }}</textarea>
            @error('peticion_oracion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Acudiente <small class="text-muted fw-normal">(si la persona es menor de edad)</small></div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Nombre del acudiente</label>
            <input type="text" name="acudiente" class="form-control @error('acudiente') is-invalid @enderror" value="{{ old('acudiente', $persona->acudiente ?? '') }}">
            @error('acudiente') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Teléfono del acudiente</label>
            <input type="text" name="telefono_acudiente" class="form-control @error('telefono_acudiente') is-invalid @enderror" value="{{ old('telefono_acudiente', $persona->telefono_acudiente ?? '') }}">
            @error('telefono_acudiente') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Parentesco</label>
            <select name="parentesco" class="form-select @error('parentesco') is-invalid @enderror">
                <option value="">Selecciona…</option>
                @foreach(['padre'=>'Padre','madre'=>'Madre','conyuge'=>'Cónyuge','abuelo_a'=>'Abuelo/a','tio_a'=>'Tío/a','hermano_a'=>'Hermano/a','tutor_legal'=>'Tutor legal','otro'=>'Otro'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('parentesco', $persona->parentesco ?? '') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('parentesco') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Red y liderazgo</div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
            <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                @foreach(['nuevo'=>'Nuevo','en_seguimiento'=>'En seguimiento','en_red'=>'En red','inactivo'=>'Inactivo'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('estado', $persona->estado ?? 'nuevo') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Red</label>
            <select name="red_id" class="form-select select2 @error('red_id') is-invalid @enderror">
                <option value="">Sin red…</option>
                @foreach($redes as $red)
                    <option value="{{ $red->id }}" @selected(old('red_id', $persona->red_id ?? '') == $red->id)>{{ $red->nombre }}</option>
                @endforeach
            </select>
            @error('red_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Líder</label>
            <select name="lider_id" class="form-select select2 @error('lider_id') is-invalid @enderror">
                <option value="">Sin líder…</option>
                @foreach($lideres as $lider)
                    <option value="{{ $lider->id }}" @selected(old('lider_id', $persona->lider_id ?? '') == $lider->id)>{{ $lider->nombre_completo }}</option>
                @endforeach
            </select>
            <small class="text-muted">
                <i class="bi bi-star-fill text-warning"></i>
                Si queda "Sin líder…" y la persona tiene Red asignada, se marca como líder principal. Para quitarle esa marca, elige aquí a qué líder pasa a reportarle.
            </small>
            @error('lider_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        @if($users->isNotEmpty())
        <div class="col-md-3">
            <label class="form-label fw-semibold">Usuario del sistema</label>
            <select name="user_id" class="form-select select2 @error('user_id') is-invalid @enderror">
                <option value="">Ninguno…</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', $persona->user_id ?? '') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Solo si esta persona debe iniciar sesión (p. ej. un líder principal).</small>
            @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        @endif
    </div>
</div>

@if($mostrarAutorizacion)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent fw-semibold">Protección de datos</div>
    <div class="card-body">
        <p class="small text-muted">
            {{ config('app.name') }} es el Responsable del tratamiento de sus datos. Al marcar la casilla de aceptación,
            la persona (o su acudiente, si es menor de edad) autoriza de manera libre, voluntaria y expresa el uso de los
            datos aquí registrados con la finalidad exclusiva de gestionar el registro de asistencia, brindar acompañamiento
            pastoral y enviar invitaciones a nuestros cultos o actividades comunitarias. Como titular, puede solicitar en
            cualquier momento la consulta, corrección o eliminación de sus datos.
        </p>
        <div class="form-check">
            <input type="checkbox" name="autorizacion_confirmada" value="1" class="form-check-input @error('autorizacion_confirmada') is-invalid @enderror" id="autorizacion_confirmada" required>
            <label class="form-check-label" for="autorizacion_confirmada">
                La persona (o su acudiente) leyó el texto anterior y autorizó el tratamiento de sus datos.
            </label>
            @error('autorizacion_confirmada') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });
});
</script>
@endpush
