<?php

namespace App\Http\Controllers;

use App\Models\CategoriaContable;
use Illuminate\Http\Request;

class CategoriaContableController extends Controller
{
    public function index()
    {
        $titulo = 'Categorías contables';
        $categorias = CategoriaContable::withCount('movimientos')->orderBy('tipo')->orderBy('nombre')->get();

        return view('modules.categorias_contables.index', compact('titulo', 'categorias'));
    }

    public function create()
    {
        $titulo = 'Crear categoría contable';

        return view('modules.categorias_contables.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate(CategoriaContable::rules());

        CategoriaContable::create($request->all());

        return redirect()->route('categorias_contables.index')->with('success', 'Categoría creada correctamente');
    }

    public function edit(CategoriaContable $categoria_contable)
    {
        $titulo = 'Editar categoría contable';
        $categoria = $categoria_contable;

        return view('modules.categorias_contables.edit', compact('titulo', 'categoria'));
    }

    public function update(Request $request, CategoriaContable $categoria_contable)
    {
        $request->validate(CategoriaContable::rules($categoria_contable->id));

        $categoria_contable->update($request->all());

        return redirect()->route('categorias_contables.index')->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(CategoriaContable $categoria_contable)
    {
        $categoria_contable->delete();

        return redirect()->route('categorias_contables.index')->with('success', 'Categoría eliminada correctamente');
    }
}
