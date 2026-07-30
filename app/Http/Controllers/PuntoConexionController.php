<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaPuntoConexion;
use App\Models\Persona;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Models\SesionPuntoConexion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PuntoConexionController extends Controller
{
    public function index()
    {
        $titulo = 'Puntos de conexión';

        $query = PuntoConexion::with(['red', 'lider', 'anfitrion'])->withCount('miembros');

        $alcanceIds = Auth::user()->alcancePersonaIds();
        if ($alcanceIds !== null) {
            $query->whereIn('lider_id', $alcanceIds);
        }

        $puntos = $query->orderBy('nombre')->get();

        return view('modules.puntos_conexion.index', compact('titulo', 'puntos'));
    }

    public function create()
    {
        $titulo = 'Crear punto de conexión';
        $redes = Red::orderBy('nombre')->get();
        $personas = $this->personasDisponibles();

        return view('modules.puntos_conexion.create', compact('titulo', 'redes', 'personas'));
    }

    public function store(Request $request)
    {
        $request->validate(PuntoConexion::rules());

        PuntoConexion::create($request->all());

        return redirect()->route('puntos_conexion.index')->with('success', 'Punto de conexión creado correctamente');
    }

    public function show(PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $titulo = $puntos_conexion->nombre;
        $puntos_conexion->load(['red', 'lider', 'anfitrion', 'miembros', 'sesiones.asistencias']);
        $personasDisponibles = $this->personasDisponibles()
            ->reject(fn (Persona $p) => $puntos_conexion->miembros->contains('id', $p->id));

        return view('modules.puntos_conexion.show', compact('titulo', 'puntos_conexion', 'personasDisponibles'));
    }

    public function edit(PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $titulo = 'Editar punto de conexión';
        $redes = Red::orderBy('nombre')->get();
        $personas = $this->personasDisponibles();

        return view('modules.puntos_conexion.edit', compact('titulo', 'puntos_conexion', 'redes', 'personas'));
    }

    public function update(Request $request, PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $request->validate(PuntoConexion::rules($puntos_conexion->id));

        $puntos_conexion->update($request->all());

        return redirect()->route('puntos_conexion.index')->with('success', 'Punto de conexión actualizado correctamente');
    }

    public function destroy(PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $puntos_conexion->delete();

        return redirect()->route('puntos_conexion.index')->with('success', 'Punto de conexión eliminado correctamente');
    }

    // ── Miembros ─────────────────────────────────────────────────────────

    public function agregarMiembro(Request $request, PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $request->validate([
            'persona_id' => ['required', 'exists:personas,id'],
            'fecha_ingreso' => ['nullable', 'date'],
        ]);

        $puntos_conexion->miembros()->syncWithoutDetaching([
            $request->persona_id => ['fecha_ingreso' => $request->fecha_ingreso ?: now()],
        ]);

        return back()->with('success', 'Miembro agregado correctamente');
    }

    public function quitarMiembro(PuntoConexion $puntos_conexion, Persona $persona)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $puntos_conexion->miembros()->detach($persona->id);

        return back()->with('success', 'Miembro quitado correctamente');
    }

    // ── Sesiones / reuniones ─────────────────────────────────────────────

    public function agregarSesion(Request $request, PuntoConexion $puntos_conexion)
    {
        $this->assertDentroDeAlcance($puntos_conexion);

        $request->validate([
            'fecha' => ['required', 'date'],
            'notas' => ['nullable', 'string'],
        ]);

        $puntos_conexion->sesiones()->create($request->only('fecha', 'notas'));

        return back()->with('success', 'Reunión registrada correctamente');
    }

    public function destroySesion(SesionPuntoConexion $sesion_punto_conexion)
    {
        $puntoConexion = $sesion_punto_conexion->puntoConexion;
        $this->assertDentroDeAlcance($puntoConexion);

        $sesion_punto_conexion->delete();

        return redirect()->route('puntos_conexion.show', $puntoConexion->id)->with('success', 'Reunión eliminada correctamente');
    }

    public function asistenciaForm(SesionPuntoConexion $sesion_punto_conexion)
    {
        $this->assertDentroDeAlcance($sesion_punto_conexion->puntoConexion);

        $titulo = 'Asistencia — '.$sesion_punto_conexion->fecha->format('d/m/Y');
        $sesion_punto_conexion->load('puntoConexion.miembros');
        $presentes = $sesion_punto_conexion->asistencias()->where('asistio', true)->pluck('persona_id')->all();

        return view('modules.puntos_conexion.asistencia', compact('titulo', 'sesion_punto_conexion', 'presentes'));
    }

    public function asistenciaStore(Request $request, SesionPuntoConexion $sesion_punto_conexion)
    {
        $this->assertDentroDeAlcance($sesion_punto_conexion->puntoConexion);

        $seleccionados = array_map('intval', $request->input('presentes', []));

        foreach ($sesion_punto_conexion->puntoConexion->miembros as $miembro) {
            AsistenciaPuntoConexion::updateOrCreate(
                [
                    'sesion_punto_conexion_id' => $sesion_punto_conexion->id,
                    'persona_id' => $miembro->id,
                ],
                [
                    'asistio' => in_array($miembro->id, $seleccionados, true),
                ]
            );
        }

        return redirect()->route('puntos_conexion.show', $sesion_punto_conexion->punto_conexion_id)
            ->with('success', 'Asistencia registrada correctamente');
    }

    private function assertDentroDeAlcance(PuntoConexion $puntoConexion): void
    {
        $alcanceIds = Auth::user()->alcancePersonaIds();

        if ($alcanceIds !== null && ! in_array($puntoConexion->lider_id, $alcanceIds, true)) {
            abort(403);
        }
    }

    private function personasDisponibles()
    {
        $alcanceIds = Auth::user()->alcancePersonaIds();

        return Persona::query()
            ->when($alcanceIds !== null, fn ($q) => $q->whereIn('id', $alcanceIds))
            ->orderBy('nombres')
            ->get();
    }
}
