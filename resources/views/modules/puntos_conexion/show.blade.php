@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-geo-alt me-2"></i>{{ $puntos_conexion->nombre }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('puntos_conexion.index') }}">Puntos de conexión</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('puntos_conexion.edit', $puntos_conexion->id) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil me-1"></i>Editar
    </a>
</div>

<section class="section mt-3">
<div class="row g-4">

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 bg-transparent fw-semibold"><i class="bi bi-info-circle me-1"></i>Datos</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Red</dt>
                    <dd class="col-7">{{ $puntos_conexion->red?->nombre ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Líder</dt>
                    <dd class="col-7">{{ $puntos_conexion->lider?->nombre_completo ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Anfitrión</dt>
                    <dd class="col-7">{{ $puntos_conexion->anfitrion?->nombre_completo ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Día</dt>
                    <dd class="col-7">{{ $puntos_conexion->dia_semana ? ucfirst($puntos_conexion->dia_semana) : '—' }}</dd>
                    <dt class="col-5 text-muted">Hora</dt>
                    <dd class="col-7">{{ $puntos_conexion->hora ? \Illuminate\Support\Carbon::parse($puntos_conexion->hora)->format('h:i A') : '—' }}</dd>
                    <dt class="col-5 text-muted">Dirección</dt>
                    <dd class="col-7">{{ $puntos_conexion->direccion ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Activo</dt>
                    <dd class="col-7">
                        <i class="bi {{ $puntos_conexion->activo ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">

        {{-- Miembros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3">
                <span class="fw-semibold"><i class="bi bi-people me-1"></i>Miembros
                    <span class="badge bg-secondary ms-1">{{ $puntos_conexion->miembros->count() }}</span>
                </span>
            </div>
            <div class="card-body border-bottom">
                <form action="{{ route('puntos_conexion.miembros.store', $puntos_conexion->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-6">
                        <select name="persona_id" class="form-select form-select-sm select2" required>
                            <option value="">Selecciona una persona…</option>
                            @foreach($personasDisponibles as $persona)
                                <option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="fecha_ingreso" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nombre</th>
                                <th>Estado</th>
                                <th>Fecha de ingreso</th>
                                <th class="text-center" style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($puntos_conexion->miembros as $miembro)
                            <tr>
                                <td class="ps-3">{{ $miembro->nombre_completo }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_',' ',$miembro->estado)) }}</span></td>
                                <td>{{ $miembro->pivot->fecha_ingreso ? \Illuminate\Support\Carbon::parse($miembro->pivot->fecha_ingreso)->format('d/m/Y') : '—' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('puntos_conexion.miembros.destroy', ['puntos_conexion' => $puntos_conexion->id, 'persona' => $miembro->id]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Quitar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay miembros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Reuniones --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <span class="fw-semibold"><i class="bi bi-calendar-check me-1"></i>Reuniones y asistencia
                    <span class="badge bg-secondary ms-1">{{ $puntos_conexion->sesiones->count() }}</span>
                </span>
            </div>
            <div class="card-body border-bottom">
                <form action="{{ route('puntos_conexion.sesiones.store', $puntos_conexion->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="date" name="fecha" class="form-control form-control-sm @error('fecha') is-invalid @enderror" value="{{ now()->format('Y-m-d') }}" required>
                        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="notas" class="form-control form-control-sm" placeholder="Notas de la reunión (opcional)">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Registrar</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Fecha</th>
                                <th>Notas</th>
                                <th class="text-center">Asistieron</th>
                                <th class="text-center" style="width:120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($puntos_conexion->sesiones as $sesion)
                            <tr>
                                <td class="ps-3">{{ $sesion->fecha->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($sesion->notas, 50) }}</td>
                                <td class="text-center">{{ $sesion->asistencias->where('asistio', true)->count() }}</td>
                                <td class="text-center">
                                    <a href="{{ route('sesiones-punto-conexion.asistencia', $sesion->id) }}" class="btn btn-outline-primary btn-sm" title="Asistencia">
                                        <i class="bi bi-clipboard-check"></i>
                                    </a>
                                    <form action="{{ route('sesiones-punto-conexion.destroy', $sesion->id) }}" method="POST" class="d-inline form-delete-item">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay reuniones registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</section>

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'default', width: '100%' });
    $('.form-delete, .form-delete-item').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Confirmar acción?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, continuar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
