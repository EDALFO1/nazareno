@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0">
            @if($persona->es_lider_principal)<i class="bi bi-star-fill text-warning" title="Líder principal"></i>@endif
            {{ $persona->nombre_completo }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('personas.index') }}">Personas</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if(Route::has('estructura-red.index') && $persona->discipulos()->exists())
        <a href="{{ route('estructura-red.index', ['lider' => $persona->id]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-share me-1"></i>Ver rama
        </a>
        @endif
        <a href="{{ route('personas.edit', $persona->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <form action="{{ route('personas.destroy', $persona->id) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $persona->nombre_completo }}">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Eliminar</button>
        </form>
    </div>
</div>

<section class="section mt-3">
<div class="row g-4">

    {{-- ── Panel izquierdo: datos ────────────────────────────────────── --}}
    <div class="col-lg-5">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold"><i class="bi bi-person me-1"></i>Datos personales</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Documento</dt>
                    <dd class="col-7 fw-semibold">{{ $persona->documento ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Teléfono</dt>
                    <dd class="col-7">{{ $persona->telefono ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Correo</dt>
                    <dd class="col-7">{{ $persona->correo ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Dirección</dt>
                    <dd class="col-7">{{ $persona->direccion ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Género</dt>
                    <dd class="col-7">{{ $persona->genero ? ucfirst($persona->genero) : '—' }}</dd>

                    <dt class="col-5 text-muted">Fecha de nacimiento</dt>
                    <dd class="col-7">{{ $persona->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Primera visita</dt>
                    <dd class="col-7">{{ $persona->fecha_primera_visita?->format('d/m/Y') ?? '—' }}</dd>
                </dl>
                @if($persona->peticion_oracion)
                <hr class="my-2">
                <div class="text-muted small mb-1">Petición de oración</div>
                <div class="small" style="white-space:pre-wrap">{{ $persona->peticion_oracion }}</div>
                @endif
            </div>
        </div>

        @if($persona->acudiente)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold"><i class="bi bi-person-heart me-1"></i>Acudiente</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Nombre</dt>
                    <dd class="col-7 fw-semibold">{{ $persona->acudiente }}</dd>
                    <dt class="col-5 text-muted">Teléfono</dt>
                    <dd class="col-7">{{ $persona->telefono_acudiente ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Parentesco</dt>
                    <dd class="col-7">{{ $persona->parentesco ? ucfirst(str_replace('_', ' ', $persona->parentesco)) : '—' }}</dd>
                </dl>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold"><i class="bi bi-share me-1"></i>Red y liderazgo</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Red</dt>
                    <dd class="col-7">{{ $persona->red?->nombre ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Líder</dt>
                    <dd class="col-7">{{ $persona->lider?->nombre_completo ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Usuario del sistema</dt>
                    <dd class="col-7">{{ $persona->user?->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        @php
                            $colores = ['nuevo' => 'info', 'en_seguimiento' => 'warning', 'en_red' => 'success', 'inactivo' => 'secondary'];
                            $etiquetas = ['nuevo' => 'Nuevo', 'en_seguimiento' => 'En seguimiento', 'en_red' => 'En red', 'inactivo' => 'Inactivo'];
                        @endphp
                        <span class="badge bg-{{ $colores[$persona->estado] }}">{{ $etiquetas[$persona->estado] }}</span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center gap-2">
                <i class="bi {{ $persona->tiene_autorizacion_datos ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger' }} fs-5"></i>
                <span class="small">
                    {{ $persona->tiene_autorizacion_datos ? 'Autorización de tratamiento de datos registrada.' : 'Falta registrar la autorización de tratamiento de datos.' }}
                </span>
            </div>
        </div>

    </div>

    {{-- ── Panel derecho: notas y procesos ───────────────────────────── --}}
    <div class="col-lg-7">

        {{-- Notas de seguimiento --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-stickies me-1"></i>Notas de seguimiento
                    <span class="badge bg-secondary ms-1">{{ $persona->notasSeguimiento->count() }}</span>
                </span>
            </div>

            <div class="card-body border-bottom">
                <form action="{{ route('personas.notas.store', $persona->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="date" name="fecha" class="form-control form-control-sm @error('fecha') is-invalid @enderror" value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-7">
                        <textarea name="nota" class="form-control form-control-sm @error('nota') is-invalid @enderror" rows="1" placeholder="Escribe una nota de seguimiento…" required></textarea>
                        @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-send"></i></button>
                    </div>
                </form>
            </div>

            <div class="card-body" style="max-height:400px; overflow-y:auto">
                @forelse($persona->notasSeguimiento as $nota)
                    <div class="d-flex gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.75rem">
                                {{ strtoupper(substr($nota->user?->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-semibold small">{{ $nota->user?->name ?? 'Usuario' }}</span>
                                <span class="text-muted" style="font-size:0.75rem">{{ $nota->fecha->format('d/m/Y') }}</span>
                            </div>
                            <div class="bg-light rounded p-2 mt-1 small" style="white-space:pre-wrap">{{ $nota->nota }}</div>
                            <form action="{{ route('personas.notas.destroy', $nota->id) }}" method="POST" class="d-inline form-delete-item">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0 mt-1" style="font-size:0.75rem">
                                    <i class="bi bi-trash me-1"></i>Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat-square-text fs-3 d-block mb-2 opacity-50"></i>
                        Aún no hay notas de seguimiento.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Procesos de formación --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <span class="fw-semibold">
                    <i class="bi bi-mortarboard me-1"></i>Procesos de formación
                    <span class="badge bg-secondary ms-1">{{ $persona->procesoParticipaciones->count() }}</span>
                </span>
            </div>

            <div class="card-body border-bottom">
                <form action="{{ route('personas.procesos.store', $persona->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-6">
                        <select name="proceso_id" class="form-select form-select-sm @error('proceso_id') is-invalid @enderror" required>
                            <option value="">Selecciona un proceso…</option>
                            @foreach($procesos as $proceso)
                                <option value="{{ $proceso->id }}">{{ $proceso->tipoProceso->nombre }} — {{ $proceso->nombre }}</option>
                            @endforeach
                        </select>
                        @error('proceso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <select name="estado_participacion" class="form-select form-select-sm">
                            <option value="en_curso">En curso</option>
                            <option value="terminado">Terminado</option>
                            <option value="incompleto">Incompleto</option>
                            <option value="retirado">Retirado</option>
                        </select>
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
                                <th class="ps-3">Tipo</th>
                                <th>Edición</th>
                                <th>Estado</th>
                                <th class="text-center" style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($persona->procesoParticipaciones as $participacion)
                            <tr>
                                <td class="ps-3">{{ $participacion->proceso->tipoProceso->nombre }}</td>
                                <td>{{ $participacion->proceso->nombre }}</td>
                                <td style="max-width:160px">
                                    <form action="{{ route('procesos.participantes.update', $participacion->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <select name="estado_participacion" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach(['en_curso'=>'En curso','terminado'=>'Terminado','incompleto'=>'Incompleto','retirado'=>'Retirado'] as $valor => $etiqueta)
                                                <option value="{{ $valor }}" @selected($participacion->estado_participacion === $valor)>{{ $etiqueta }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('procesos.participantes.destroy', $participacion->id) }}" method="POST" class="form-delete-item">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Quitar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No participa en ningún proceso todavía.</td></tr>
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
    $('.form-delete, .form-delete-item').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).data('nombre') || 'este registro';
        Swal.fire({
            title: '¿Eliminar registro?',
            text: `«${nombre}» será eliminado permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
