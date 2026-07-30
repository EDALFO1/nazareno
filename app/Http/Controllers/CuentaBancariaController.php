<?php

namespace App\Http\Controllers;

use App\Models\CuentaBancaria;
use Illuminate\Http\Request;

class CuentaBancariaController extends Controller
{
    public function index()
    {
        $titulo = 'Cuentas bancarias';
        $cuentas = CuentaBancaria::orderBy('nombre')->get();

        return view('modules.cuentas_bancarias.index', compact('titulo', 'cuentas'));
    }

    public function create()
    {
        $titulo = 'Crear cuenta bancaria';

        return view('modules.cuentas_bancarias.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $request->validate(CuentaBancaria::rules());

        CuentaBancaria::create($request->all());

        return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta bancaria creada correctamente');
    }

    public function edit(CuentaBancaria $cuenta_bancaria)
    {
        $titulo = 'Editar cuenta bancaria';
        $cuenta = $cuenta_bancaria;

        return view('modules.cuentas_bancarias.edit', compact('titulo', 'cuenta'));
    }

    public function update(Request $request, CuentaBancaria $cuenta_bancaria)
    {
        $request->validate(CuentaBancaria::rules($cuenta_bancaria->id));

        $cuenta_bancaria->update($request->all());

        return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta bancaria actualizada correctamente');
    }

    public function destroy(CuentaBancaria $cuenta_bancaria)
    {
        $cuenta_bancaria->delete();

        return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta bancaria eliminada correctamente');
    }
}
