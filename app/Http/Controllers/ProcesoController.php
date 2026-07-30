<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Persona;
use App\Models\Proceso;
use App\Models\ProcesoParticipante;
use App\Models\Red;
use App\Models\SesionProceso;
use App\Models\TipoProceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcesoController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Procesos de formación';

        $query = Proceso::with('tipoProceso')->withCount('participantes');

        if ($request->filled('tipo_proceso_id')) {
            $query->where('tipo_proceso_id', $request->tipo_proceso_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $procesos = $query->orderByDesc('fecha_inicio')->get();
        $tiposProceso = TipoProceso::orderBy('orden')->get();

        return view('modules.procesos.index', compact('titulo', 'procesos', 'tiposProceso'));
    }

    public function create()
    {
        $titulo = 'Crear proceso';
        $tiposProceso = TipoProceso::orderBy('orden')->get();
        $procesosAnteriores = Proceso::with('tipoProceso')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(fn (Proceso $p) => [
                'id' => $p->id,
                'label' => "{$p->tipoProceso->nombre} — {$p->nombre} ({$p->participantes()->where('estado_participacion', 'terminado')->count()} terminaron)",
            ]);

        return view('modules.procesos.create', compact('titulo', 'tiposProceso', 'procesosAnteriores'));
    }

    public function store(Request $request)
    {
        $request->validate(Proceso::rules() + [
            'cargar_desde_proceso_id' => ['nullable', 'exists:procesos,id'],
        ]);

        $proceso = Proceso::create($request->only(['tipo_proceso_id', 'nombre', 'fecha_inicio', 'estado']));

        if ($request->filled('cargar_desde_proceso_id')) {
            $anterior = Proceso::find($request->cargar_desde_proceso_id);

            foreach ($anterior->participantes()->where('estado_participacion', 'terminado')->get() as $participante) {
                ProcesoParticipante::firstOrCreate(
                    ['proceso_id' => $proceso->id, 'persona_id' => $participante->persona_id],
                    ['red_id' => $participante->red_id, 'estado_participacion' => 'en_curso']
                );
            }
        }

        return redirect()->route('procesos.show', $proceso)->with('success', 'Proceso creado correctamente');
    }

    public function show(Proceso $proceso)
    {
        $titulo = $proceso->nombre;
        $proceso->load(['tipoProceso', 'participantes.persona', 'participantes.red', 'participantes.sesionRetiro', 'sesiones.asistencias']);

        $alcanceIds = Auth::user()->alcancePersonaIds();
        $personas = Persona::query()
            ->when($alcanceIds !== null, fn ($q) => $q->whereIn('id', $alcanceIds))
            ->orderBy('nombres')
            ->get();
        $redes = Red::orderBy('nombre')->get();

        return view('modules.procesos.show', compact('titulo', 'proceso', 'personas', 'redes'));
    }

    public function edit(Proceso $proceso)
    {
        $titulo = 'Editar proceso';
        $tiposProceso = TipoProceso::orderBy('orden')->get();

        return view('modules.procesos.edit', compact('titulo', 'proceso', 'tiposProceso'));
    }

    public function update(Request $request, Proceso $proceso)
    {
        $request->validate(Proceso::rules($proceso->id));

        $proceso->update($request->all());

        return redirect()->route('procesos.show', $proceso)->with('success', 'Proceso actualizado correctamente');
    }

    public function destroy(Proceso $proceso)
    {
        $proceso->delete();

        return redirect()->route('procesos.index')->with('success', 'Proceso eliminado correctamente');
    }

    // ── Sesiones ─────────────────────────────────────────────────────────

    public function agregarSesion(Request $request, Proceso $proceso)
    {
        $request->validate([
            'numero_sesion' => ['required', 'integer', 'min:1'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
        ]);

        $proceso->sesiones()->create($request->only('numero_sesion', 'nombre', 'fecha'));

        return back()->with('success', 'Sesión agregada correctamente');
    }

    public function generarSesiones(Proceso $proceso)
    {
        $total = $proceso->tipoProceso->numero_sesiones ?? 0;
        $existentes = $proceso->sesiones()->pluck('numero_sesion')->all();

        for ($i = 1; $i <= $total; $i++) {
            if (! in_array($i, $existentes, true)) {
                $proceso->sesiones()->create(['numero_sesion' => $i]);
            }
        }

        return back()->with('success', 'Sesiones generadas correctamente');
    }

    public function destroySesion(SesionProceso $sesion_proceso)
    {
        $proceso = $sesion_proceso->proceso;
        $sesion_proceso->delete();

        return redirect()->route('procesos.show', $proceso->id)->with('success', 'Sesión eliminada correctamente');
    }

    public function asistenciaForm(SesionProceso $sesion_proceso)
    {
        $titulo = 'Asistencia — Sesión '.$sesion_proceso->numero_sesion;
        $sesion_proceso->load('proceso.participantes.persona');
        $presentes = $sesion_proceso->asistencias()->where('asistio', true)->pluck('persona_id')->all();

        return view('modules.procesos.asistencia', compact('titulo', 'sesion_proceso', 'presentes'));
    }

    public function asistenciaStore(Request $request, SesionProceso $sesion_proceso)
    {
        $seleccionados = array_map('intval', $request->input('presentes', []));

        foreach ($sesion_proceso->proceso->participantes as $participante) {
            Asistencia::updateOrCreate(
                ['sesion_proceso_id' => $sesion_proceso->id, 'persona_id' => $participante->persona_id],
                ['asistio' => in_array($participante->persona_id, $seleccionados, true)]
            );
        }

        return redirect()->route('procesos.show', $sesion_proceso->proceso_id)->with('success', 'Asistencia registrada correctamente');
    }

    // ── Participantes ────────────────────────────────────────────────────

    public function agregarParticipante(Request $request, Proceso $proceso)
    {
        $request->validate([
            'persona_id' => ['required', 'exists:personas,id'],
            'red_id' => ['nullable', 'exists:redes,id'],
            'estado_participacion' => ['required', 'in:en_curso,terminado,incompleto,retirado'],
        ]);

        $this->assertDentroDeAlcance(Persona::findOrFail($request->persona_id));

        $proceso->participantes()->create($request->only('persona_id', 'red_id', 'estado_participacion'));

        return back()->with('success', 'Participante agregado correctamente');
    }

    public function actualizarParticipante(Request $request, ProcesoParticipante $proceso_participante)
    {
        $this->assertDentroDeAlcance($proceso_participante->persona);

        $request->validate([
            'estado_participacion' => ['required', 'in:en_curso,terminado,incompleto,retirado'],
            'sesion_retiro_id' => ['nullable', 'exists:sesiones_proceso,id'],
        ]);

        $proceso_participante->update($request->only('estado_participacion', 'sesion_retiro_id'));

        return back()->with('success', 'Participante actualizado correctamente');
    }

    public function destroyParticipante(ProcesoParticipante $proceso_participante)
    {
        $this->assertDentroDeAlcance($proceso_participante->persona);

        $proceso_participante->delete();

        return back()->with('success', 'Participante eliminado correctamente');
    }

    private function assertDentroDeAlcance(Persona $persona): void
    {
        $alcanceIds = Auth::user()->alcancePersonaIds();

        if ($alcanceIds !== null && ! in_array($persona->id, $alcanceIds, true)) {
            abort(403);
        }
    }

    public function marcarTerminacionForm(Proceso $proceso)
    {
        $titulo = 'Marcar quiénes terminaron — '.$proceso->nombre;
        $proceso->load('participantes.persona');
        $terminados = $proceso->participantes()->where('estado_participacion', 'terminado')->pluck('persona_id')->all();

        return view('modules.procesos.marcar_terminacion', compact('titulo', 'proceso', 'terminados'));
    }

    public function marcarTerminacion(Request $request, Proceso $proceso)
    {
        $seleccionados = array_map('intval', $request->input('terminaron', []));

        foreach ($proceso->participantes as $participante) {
            if ($participante->estado_participacion === 'retirado') {
                continue;
            }

            $participante->update([
                'estado_participacion' => in_array($participante->persona_id, $seleccionados, true)
                    ? 'terminado'
                    : 'incompleto',
            ]);
        }

        return redirect()->route('procesos.show', $proceso->id)->with('success', 'Terminación actualizada correctamente');
    }
}
