<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $titulo = 'Proveedores';
        $proveedores = Proveedor::withCount('movimientos')->orderBy('nombre')->get();

        return view('modules.proveedores.index', compact('titulo', 'proveedores'));
    }

    public function create()
    {
        $titulo = 'Crear proveedor';

        return view('modules.proveedores.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate(Proveedor::rules());

        Proveedor::create($request->all());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente');
    }

    public function edit(Proveedor $proveedor)
    {
        $titulo = 'Editar proveedor';

        return view('modules.proveedores.edit', compact('titulo', 'proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate(Proveedor::rules($proveedor->id));

        $proveedor->update($request->all());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente');
    }
}
