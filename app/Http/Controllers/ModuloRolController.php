<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Rol;
use Illuminate\Http\Request;

class ModuloRolController extends Controller
{
    public function index()
    {
        $titulo = 'Módulos por rol';
        $roles = Rol::withCount('modulos')->orderBy('nombre')->paginate(20);

        return view('modules.modulos_rol.index', compact('titulo', 'roles'));
    }

    public function edit(Rol $rol)
    {
        $titulo = 'Módulos — '.$rol->nombre;
        $modulos = Modulo::where('activo', true)->orderBy('grupo')->orderBy('orden')->get()->groupBy('grupo');
        $asignados = $rol->modulos()->pluck('modulos.id')->toArray();

        return view('modules.modulos_rol.edit', compact('titulo', 'rol', 'modulos', 'asignados'));
    }

    public function update(Request $request, Rol $rol)
    {
        $rol->modulos()->sync($request->input('modulos', []));

        return redirect()->route('modulos-rol.index')
            ->with('success', "Módulos del rol «{$rol->nombre}» actualizados correctamente.");
    }
}
