<?php

namespace App\Http\Controllers;

use App\Models\Red;
use Illuminate\Http\Request;

class RedController extends Controller
{
    public function index()
    {
        $titulo = 'Redes';
        $redes = Red::withCount(['personas', 'lideresPrincipales', 'puntosConexion'])->orderBy('nombre')->get();

        return view('modules.redes.index', compact('titulo', 'redes'));
    }

    public function create()
    {
        $titulo = 'Crear red';

        return view('modules.redes.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate(Red::rules(), [
            'nombre.unique' => 'Ya existe una red con ese nombre.',
        ]);

        Red::create($request->all());

        return redirect()->route('redes.index')->with('success', 'Red creada correctamente');
    }

    public function edit(Red $red)
    {
        $titulo = 'Editar red';

        return view('modules.redes.edit', compact('titulo', 'red'));
    }

    public function update(Request $request, Red $red)
    {
        $request->validate(Red::rules($red->id), [
            'nombre.unique' => 'Ya existe una red con ese nombre.',
        ]);

        $red->update($request->all());

        return redirect()->route('redes.index')->with('success', 'Red actualizada correctamente');
    }

    public function destroy(Red $red)
    {
        $red->delete();

        return redirect()->route('redes.index')->with('success', 'Red eliminada correctamente');
    }
}
