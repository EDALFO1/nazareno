<?php

namespace App\Http\Controllers;

use App\Models\Red;
use App\Models\TipoProceso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $titulo = 'Dashboard';

        $verEstadisticas = Auth::user()->hasAnyRol(['super_admin', 'admin_general']);

        $pipelineProcesos = collect();
        $resumenRedes = collect();

        if ($verEstadisticas) {
            $pipelineProcesos = TipoProceso::query()
                ->orderBy('orden')
                ->withCount(['procesos as ediciones_count'])
                ->get()
                ->map(function (TipoProceso $tipo) {
                    return [
                        'nombre' => $tipo->nombre,
                        'ediciones' => $tipo->ediciones_count,
                        'en_curso' => $tipo->procesos()
                            ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'en_curso')])
                            ->get()->sum('participantes_count'),
                        'terminados' => $tipo->procesos()
                            ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'terminado')])
                            ->get()->sum('participantes_count'),
                        'incompletos' => $tipo->procesos()
                            ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'incompleto')])
                            ->get()->sum('participantes_count'),
                        'retirados' => $tipo->procesos()
                            ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'retirado')])
                            ->get()->sum('participantes_count'),
                    ];
                });

            $resumenRedes = Red::query()
                ->withCount([
                    'personas',
                    'personas as lideres_count' => fn (Builder $query) => $query->whereHas('discipulos'),
                    'personas as nuevos_count' => fn (Builder $query) => $query->where('estado', 'nuevo'),
                    'personas as en_seguimiento_count' => fn (Builder $query) => $query->where('estado', 'en_seguimiento'),
                    'personas as en_red_count' => fn (Builder $query) => $query->where('estado', 'en_red'),
                    'puntosConexion',
                ])
                ->get();
        }

        return view('modules.dashboard.home', compact('titulo', 'verEstadisticas', 'pipelineProcesos', 'resumenRedes'));
    }
}
