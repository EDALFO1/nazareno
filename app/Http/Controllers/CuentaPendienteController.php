<?php

namespace App\Http\Controllers;

use App\Models\CategoriaContable;
use App\Models\CuentaBancaria;
use App\Models\CuentaPendiente;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CuentaPendienteController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Cuentas por cobrar/pagar';

        $query = CuentaPendiente::with(['categoriaContable', 'persona']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->boolean('con_saldo')) {
            $query->whereRaw(
                'monto_total > (select coalesce(sum(monto), 0) from movimientos_contables where movimientos_contables.cuenta_pendiente_id = cuentas_pendientes.id)'
            );
        }

        $cuentas = $query->orderBy('fecha_vencimiento')->get();

        return view('modules.cuentas_pendientes.index', compact('titulo', 'cuentas'));
    }

    public function create()
    {
        $titulo = 'Crear cuenta pendiente';

        return view('modules.cuentas_pendientes.create', array_merge(compact('titulo'), $this->datosFormulario()));
    }

    public function store(Request $request)
    {
        $request->validate(CuentaPendiente::rules());

        CuentaPendiente::create($request->all());

        return redirect()->route('cuentas_pendientes.index')->with('success', 'Cuenta pendiente creada correctamente');
    }

    public function show(CuentaPendiente $cuentas_pendiente)
    {
        $titulo = $cuentas_pendiente->descripcion;
        $cuentas_pendiente->load(['categoriaContable', 'persona', 'movimientos' => fn ($q) => $q->orderByDesc('fecha')]);
        $cuentasBancarias = CuentaBancaria::where('activa', true)->orderBy('nombre')->get();

        return view('modules.cuentas_pendientes.show', compact('titulo', 'cuentas_pendiente', 'cuentasBancarias'));
    }

    public function edit(CuentaPendiente $cuentas_pendiente)
    {
        $titulo = 'Editar cuenta pendiente';
        $cuenta = $cuentas_pendiente;

        return view('modules.cuentas_pendientes.edit', array_merge(compact('titulo', 'cuenta'), $this->datosFormulario()));
    }

    public function update(Request $request, CuentaPendiente $cuentas_pendiente)
    {
        $request->validate(CuentaPendiente::rules($cuentas_pendiente->id));

        $cuentas_pendiente->update($request->all());

        return redirect()->route('cuentas_pendientes.index')->with('success', 'Cuenta pendiente actualizada correctamente');
    }

    public function destroy(CuentaPendiente $cuentas_pendiente)
    {
        $cuentas_pendiente->delete();

        return redirect()->route('cuentas_pendientes.index')->with('success', 'Cuenta pendiente eliminada correctamente');
    }

    public function agregarAbono(Request $request, CuentaPendiente $cuentas_pendiente)
    {
        $request->validate([
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:efectivo,consignacion,transferencia,cheque'],
            'cuenta_bancaria_id' => ['nullable', 'exists:cuentas_bancarias,id'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $cuentas_pendiente->movimientos()->create([
            'tipo' => $cuentas_pendiente->tipo === 'por_cobrar' ? 'ingreso' : 'egreso',
            'categoria_contable_id' => $cuentas_pendiente->categoria_contable_id,
            'persona_id' => $cuentas_pendiente->persona_id,
            'fecha' => $request->fecha,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'cuenta_bancaria_id' => $request->cuenta_bancaria_id,
            'descripcion' => $request->descripcion,
            'registrado_por_id' => Auth::id(),
        ]);

        return back()->with('success', 'Abono registrado correctamente');
    }

    private function datosFormulario(): array
    {
        return [
            'categoriasIngreso' => CategoriaContable::where('activo', true)->where('tipo', 'ingreso')->orderBy('nombre')->get(),
            'categoriasEgreso' => CategoriaContable::where('activo', true)->where('tipo', 'egreso')->orderBy('nombre')->get(),
            'personas' => Persona::orderBy('nombres')->get(),
        ];
    }
}
