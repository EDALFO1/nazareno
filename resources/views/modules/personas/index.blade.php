@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-people-fill me-2"></i>Personas</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Personas</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('personas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nueva
    </a>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('personas.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="buscar" class="form-control" placeholder="Nombre, apellido o documento" value="{{ request('buscar') }}">
            </div>
            <div class="col-lg-3 col-md-3">
                <label class="form-label small fw-semibold mb-1">Red</label>
                <select name="red_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($redes as $red)
                        <option value="{{ $red->id }}" @selected(request('red_id') == $red->id)>{{ $red->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['nuevo'=>'Nuevo','en_seguimiento'=>'En seguimiento','en_red'=>'En red','inactivo'=>'Inactivo'] as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-12 d-flex gap-3 flex-wrap">
                <div class="form-check">
                    <input type="checkbox" name="lideres_principales" value="1" class="form-check-input" id="fLideres" @checked(request('lideres_principales'))>
                    <label class="form-check-label small" for="fLideres">Solo líderes principales</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="sin_autorizacion" value="1" class="form-check-input" id="fAutorizacion" @checked(request('sin_autorizacion'))>
                    <label class="form-check-label small" for="fAutorizacion">Sin autorización de datos</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Red</th>
                        <th>Líder</th>
                        <th>Estado</th>
                        <th class="text-center">Datos</th>
                        <th class="text-center" style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personas as $persona)
                    <tr>
                        <td class="ps-3">
                            @if($persona->es_lider_principal)
                                <i class="bi bi-star-fill text-warning" title="Líder principal"></i>
                            @endif
                            {{ $persona->nombre_completo }}
                            @if($persona->etiqueta_linea)
                                <span class="badge bg-light text-dark border ms-1">{{ $persona->etiqueta_linea }}</span>
                            @endif
                        </td>
                        <td>{{ $persona->documento ?? '—' }}</td>
                        <td>{{ $persona->telefono ?? '—' }}</td>
                        <td>
                            @if($persona->red)
                                <span class="badge bg-light text-dark border">{{ $persona->red->nombre }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $persona->lider?->nombre_completo ?? '—' }}</td>
                        <td>
                            @php
                                $colores = ['nuevo' => 'info', 'en_seguimiento' => 'warning', 'en_red' => 'success', 'inactivo' => 'secondary'];
                                $etiquetas = ['nuevo' => 'Nuevo', 'en_seguimiento' => 'En seguimiento', 'en_red' => 'En red', 'inactivo' => 'Inactivo'];
                            @endphp
                            <span class="badge bg-{{ $colores[$persona->estado] }}">{{ $etiquetas[$persona->estado] }}</span>
                        </td>
                        <td class="text-center">
                            <i class="bi {{ $persona->tiene_autorizacion_datos ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger' }}"
                               title="{{ $persona->tiene_autorizacion_datos ? 'Autorización de tratamiento de datos registrada.' : 'Falta registrar la autorización de tratamiento de datos.' }}"></i>
                        </td>
                        <td class="text-center">
                            @if(Route::has('estructura-red.index') && $persona->discipulos()->exists())
                            <a href="{{ route('estructura-red.index', ['lider' => $persona->id]) }}" class="btn btn-outline-secondary btn-sm" title="Ver rama">
                                <i class="bi bi-share"></i>
                            </a>
                            @endif
                            <a href="{{ route('personas.show', $persona->id) }}" class="btn btn-outline-primary btn-sm" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('personas.edit', $persona->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay personas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $personas->links() }}
        </div>
    </div>
</div>

</section>

@endsection
