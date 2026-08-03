@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-grid-1x2-fill me-2"></i>Inicio </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item active">Inicio  </li>
        </ol>
    </nav>
</div>

<section class="section mt-3">

    @if(! $verEstadisticas)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-emoji-smile fs-1 d-block mb-3 opacity-50"></i>
                Bienvenido, {{ auth()->user()->name }}. Usa el menú lateral para navegar a tus módulos.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0">Pipeline de procesos de formación</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Proceso</th>
                                <th class="text-center">Ediciones creadas</th>
                                <th class="text-center">Personas en curso</th>
                                <th class="text-center">Terminaron</th>
                                <th class="text-center">Incompletos</th>
                                <th class="text-center">Retirados</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pipelineProcesos as $fila)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $fila['nombre'] }}</td>
                                <td class="text-center">{{ $fila['ediciones'] }}</td>
                                <td class="text-center">{{ $fila['en_curso'] }}</td>
                                <td class="text-center">{{ $fila['terminados'] }}</td>
                                <td class="text-center">{{ $fila['incompletos'] }}</td>
                                <td class="text-center">{{ $fila['retirados'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No hay procesos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0">Resumen por red</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Red</th>
                                <th class="text-center">Personas</th>
                                <th class="text-center">Líderes</th>
                                <th class="text-center">Puntos de conexión</th>
                                <th class="text-center">Nuevos</th>
                                <th class="text-center">En seguimiento</th>
                                <th class="text-center">En red</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumenRedes as $red)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $red->nombre }}</td>
                                <td class="text-center">{{ $red->personas_count }}</td>
                                <td class="text-center">{{ $red->lideres_count }}</td>
                                <td class="text-center">{{ $red->puntos_conexion_count }}</td>
                                <td class="text-center"><span class="badge bg-info">{{ $red->nuevos_count }}</span></td>
                                <td class="text-center"><span class="badge bg-warning">{{ $red->en_seguimiento_count }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $red->en_red_count }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No hay redes registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</section>

@endsection
