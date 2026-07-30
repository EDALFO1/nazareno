<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $titulo = 'Roles';
        $roles = Rol::withCount('users')->orderBy('nombre')->paginate(20);

        return view('modules.roles.index', compact('titulo', 'roles'));
    }

    public function create()
    {
        $titulo = 'Crear rol';

        return view('modules.roles.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate(Rol::rules(), [
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        Rol::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente');
    }

    public function edit(Rol $role)
    {
        $titulo = 'Editar rol';

        return view('modules.roles.edit', compact('titulo', 'role'));
    }

    public function update(Request $request, Rol $role)
    {
        $request->validate(Rol::rules($role->id), [
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        $role->update($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
    }

    public function destroy(Rol $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
    }
}
