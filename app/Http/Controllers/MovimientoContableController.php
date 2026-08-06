<?php

namespace App\Http\Controllers;

use App\Models\CategoriaContable;
use App\Models\CuentaBancaria;
use App\Models\MovimientoContable;
use App\Models\Persona;
use App\Models\Proveedor;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Services\DiezmoDeDiezmosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MovimientoContableController extends Controller
{
    public function index(Request $request, DiezmoDeDiezmosService $diezmoService)
    {
        $titulo = 'Movimientos contables';

        $query = MovimientoContable::with(['categoriaContable', 'persona', 'proveedor', 'red', 'cuentaBancaria']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('categoria_contable_id')) {
            $query->where('categoria_contable_id', $request->categoria_contable_id);
        }
        if ($request->filled('red_id')) {
            $query->where('red_id', $request->red_id);
        }
        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $movimientos = $query->orderByDesc('fecha')->paginate(25)->withQueryString();
        $categorias = CategoriaContable::where('activo', true)->orderBy('nombre')->get();
        $redes = Red::orderBy('nombre')->get();

        $mesActual = now();
        $diezmosDelMes = $diezmoService->totalBaseDelMes($mesActual);
        $cuentaDiezmoDelMes = $diezmoService->cuentaDelMes($mesActual);

        return view('modules.movimientos_contables.index', compact(
            'titulo', 'movimientos', 'categorias', 'redes', 'mesActual', 'diezmosDelMes', 'cuentaDiezmoDelMes'
        ));
    }

    public function create()
    {
        $titulo = 'Registrar movimiento contable';

        return view('modules.movimientos_contables.create', array_merge(compact('titulo'), $this->datosFormulario()));
    }

    public function store(Request $request, DiezmoDeDiezmosService $diezmoService)
    {
        $request->validate(MovimientoContable::rules());

        $datos = $request->except(['_token', 'comprobante']);
        $datos['registrado_por_id'] = Auth::id();

        if ($request->hasFile('comprobante')) {
            $datos['comprobante'] = $request->file('comprobante')->store('comprobantes', 'local');
        }

        $movimiento = MovimientoContable::create($datos);

        $mensaje = 'Movimiento registrado correctamente';

        if ($diezmoService->esIngresoBase($movimiento)) {
            $obligacion = $diezmoService->sincronizarMes($movimiento->fecha);
            $mensaje .= '. Diezmo de diezmos (15%) de '.ucfirst($movimiento->fecha->translatedFormat('F Y'))
                .' actualizado a $'.number_format($obligacion, 0, ',', '.').' en Cuentas pendientes.';
        } else {
            $diezmoService->vincularPagoSiCorresponde($movimiento);
        }

        return redirect()->route('movimientos_contables.create')->with('success', $mensaje);
    }

    public function edit(MovimientoContable $movimientos_contable)
    {
        $titulo = 'Editar movimiento contable';
        $movimiento = $movimientos_contable;

        return view('modules.movimientos_contables.edit', array_merge(compact('titulo', 'movimiento'), $this->datosFormulario()));
    }

    public function update(Request $request, MovimientoContable $movimientos_contable, DiezmoDeDiezmosService $diezmoService)
    {
        $request->validate(MovimientoContable::rules($movimientos_contable->id));

        $fechaAnterior = $movimientos_contable->fecha->copy();

        $datos = $request->except(['_token', '_method', 'comprobante']);

        if ($request->hasFile('comprobante')) {
            if ($movimientos_contable->comprobante) {
                Storage::disk('local')->delete($movimientos_contable->comprobante);
            }
            $datos['comprobante'] = $request->file('comprobante')->store('comprobantes', 'local');
        }

        $movimientos_contable->update($datos);

        // Recalcula siempre el mes anterior y el nuevo (por si cambió la
        // categoría, el monto o la fecha): así la Cuenta Pendiente del
        // Diezmo de Diezmos nunca queda desincronizada de la realidad.
        $diezmoService->sincronizarMes($fechaAnterior);
        $diezmoService->sincronizarMes($movimientos_contable->fecha);
        $diezmoService->vincularPagoSiCorresponde($movimientos_contable);

        return redirect()->route('movimientos_contables.index')->with('success', 'Movimiento actualizado correctamente');
    }

    public function destroy(MovimientoContable $movimientos_contable, DiezmoDeDiezmosService $diezmoService)
    {
        if ($movimientos_contable->comprobante) {
            Storage::disk('local')->delete($movimientos_contable->comprobante);
        }

        $fecha = $movimientos_contable->fecha->copy();
        $movimientos_contable->delete();

        $diezmoService->sincronizarMes($fecha);

        return back()->with('success', 'Movimiento eliminado correctamente');
    }

    public function verComprobante(MovimientoContable $movimientos_contable)
    {
        abort_unless($movimientos_contable->comprobante, 404);

        return redirect()->away(
            Storage::disk('local')->temporaryUrl($movimientos_contable->comprobante, now()->addMinutes(5))
        );
    }

    private function datosFormulario(): array
    {
        return [
            'categorias' => CategoriaContable::where('activo', true)->orderBy('nombre')->get(),
            'personas' => Persona::orderBy('nombres')->get(),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'redes' => Red::orderBy('nombre')->get(),
            'puntosConexion' => PuntoConexion::orderBy('nombre')->get(),
            'cuentasBancarias' => CuentaBancaria::where('activa', true)->orderBy('nombre')->get(),
        ];
    }
}
