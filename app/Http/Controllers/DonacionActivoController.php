<?php

namespace App\Http\Controllers;

use App\Models\DonacionActivo;
use App\Models\Persona;
use Illuminate\Http\Request;

class DonacionActivoController extends Controller
{
    public function index()
    {
        $titulo = 'Donaciones de activos';
        $donaciones = DonacionActivo::with('persona')->orderByDesc('fecha')->get();

        return view('modules.donaciones_activos.index', compact('titulo', 'donaciones'));
    }

    public function create()
    {
        $titulo = 'Registrar donación de activo';
        $personas = Persona::orderBy('nombres')->get();

        return view('modules.donaciones_activos.create', compact('titulo', 'personas'));
    }

    public function store(Request $request)
    {
        $request->validate(DonacionActivo::rules());

        DonacionActivo::create($request->all());

        return redirect()->route('donaciones_activos.index')->with('success', 'Donación registrada correctamente');
    }

    public function edit(DonacionActivo $donaciones_activo)
    {
        $titulo = 'Editar donación de activo';
        $donacion = $donaciones_activo;
        $personas = Persona::orderBy('nombres')->get();

        return view('modules.donaciones_activos.edit', compact('titulo', 'donacion', 'personas'));
    }

    public function update(Request $request, DonacionActivo $donaciones_activo)
    {
        $request->validate(DonacionActivo::rules($donaciones_activo->id));

        $donaciones_activo->update($request->all());

        return redirect()->route('donaciones_activos.index')->with('success', 'Donación actualizada correctamente');
    }

    public function destroy(DonacionActivo $donaciones_activo)
    {
        $donaciones_activo->delete();

        return redirect()->route('donaciones_activos.index')->with('success', 'Donación eliminada correctamente');
    }
}
