<?php

namespace App\Http\Controllers;

use App\Services\AlertasService;
use Illuminate\Support\Facades\Auth;

class AlertaController extends Controller
{
    public function index(AlertasService $servicio)
    {
        $titulo = 'Alertas';
        $alcanceIds = Auth::user()->alcancePersonaIds();

        $personasSinRetomar = $servicio->personasSinRetomar($alcanceIds);
        $puntosSinReportar = $servicio->puntosSinReportar($alcanceIds);
        $cumpleanosDelMes = $servicio->cumpleanosDelMes($alcanceIds);

        return view('modules.alertas.index', compact('titulo', 'personasSinRetomar', 'puntosSinReportar', 'cumpleanosDelMes'));
    }
}
