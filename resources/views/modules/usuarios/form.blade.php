@php $usuario = $usuario ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $usuario->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Contraseña @if(! $usuario)<span class="text-danger">*</span>@endif</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $usuario ? '' : 'required' }}>
        @if($usuario)
            <small class="text-muted">Déjala vacía para no cambiar la contraseña actual.</small>
        @endif
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
        <select name="rol_id" class="form-select @error('rol_id') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            @foreach($roles as $rol)
                <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario->rol_id ?? '') == $rol->id)>{{ $rol->nombre }}</option>
            @endforeach
        </select>
        @error('rol_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
