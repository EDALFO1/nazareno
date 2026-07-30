<?php

namespace App\Http\Controllers;

use App\Models\NotaSeguimiento;
use App\Models\Persona;
use App\Models\Proceso;
use App\Models\Red;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Personas';

        $query = Persona::query()->with(['red', 'lider']);

        $alcanceIds = Auth::user()->alcancePersonaIds();
        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('red_id')) {
            $query->where('red_id', $request->red_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->boolean('lideres_principales')) {
            $query->whereNotNull('red_id')->whereNull('lider_id');
        }

        if ($request->boolean('sin_autorizacion')) {
            $query->whereDoesntHave('autorizacionesTratamientoDatos');
        }

        $ids = Persona::idsEnOrdenJerarquico();
        if ($ids) {
            $casos = collect($ids)->map(fn (int $id, int $posicion) => "WHEN {$id} THEN {$posicion}")->implode(' ');
            $query->orderByRaw("CASE id {$casos} ELSE ".count($ids).' END');
        }

        $personas = $query->paginate(25)->withQueryString();
        $redes = Red::orderBy('nombre')->get();

        return view('modules.personas.index', compact('titulo', 'personas', 'redes'));
    }

    public function create()
    {
        $titulo = 'Crear persona';
        $redes = Red::orderBy('nombre')->get();
        $lideres = Persona::orderBy('nombres')->get();
        $users = Auth::user()->hasRol('lider_red') ? collect() : User::orderBy('name')->get();

        return view('modules.personas.create', compact('titulo', 'redes', 'lideres', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate(
            Persona::rules() + ['autorizacion_confirmada' => ['accepted']],
            [
                'autorizacion_confirmada.accepted' => 'Debes confirmar la autorización de tratamiento de datos.',
            ]
        );

        $datos = $request->except(['autorizacion_confirmada', '_token']);

        if (Auth::user()->hasRol('lider_red')) {
            unset($datos['user_id']);
        }

        $persona = Persona::create($datos);

        $persona->autorizacionesTratamientoDatos()->create([
            'canal' => 'registro_manual',
            'registrado_por_user_id' => Auth::id(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('personas.show', $persona)->with('success', 'Persona creada correctamente');
    }

    public function show(Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $titulo = $persona->nombre_completo;
        $persona->load([
            'red', 'lider', 'user',
            'notasSeguimiento.user',
            'procesoParticipaciones.proceso.tipoProceso',
        ]);
        $procesos = Proceso::with('tipoProceso')->orderByDesc('fecha_inicio')->get();

        return view('modules.personas.show', compact('titulo', 'persona', 'procesos'));
    }

    public function edit(Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $titulo = 'Editar persona';
        $redes = Red::orderBy('nombre')->get();
        $lideres = Persona::where('id', '!=', $persona->id)->orderBy('nombres')->get();
        $users = Auth::user()->hasRol('lider_red') ? collect() : User::orderBy('name')->get();
        $exigirAutorizacion = ! $persona->tiene_autorizacion_datos;

        return view('modules.personas.edit', compact('titulo', 'persona', 'redes', 'lideres', 'users', 'exigirAutorizacion'));
    }

    public function update(Request $request, Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $exigirAutorizacion = ! $persona->tiene_autorizacion_datos;

        $rules = Persona::rules($persona->id);
        if ($exigirAutorizacion) {
            $rules['autorizacion_confirmada'] = ['accepted'];
        }

        $request->validate($rules, [
            'autorizacion_confirmada.accepted' => 'Debes confirmar la autorización de tratamiento de datos.',
        ]);

        $datos = $request->except(['autorizacion_confirmada', '_token', '_method']);

        if (Auth::user()->hasRol('lider_red')) {
            unset($datos['user_id']);
        }

        $persona->update($datos);

        if ($exigirAutorizacion) {
            $persona->autorizacionesTratamientoDatos()->create([
                'canal' => 'registro_manual',
                'registrado_por_user_id' => Auth::id(),
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('personas.show', $persona)->with('success', 'Persona actualizada correctamente');
    }

    public function destroy(Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Persona eliminada correctamente');
    }

    public function buscar(Request $request)
    {
        $buscar = (string) $request->get('q', '');

        $query = Persona::query()->orderBy('nombres');

        $alcanceIds = Auth::user()->alcancePersonaIds();
        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        if ($buscar !== '') {
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        return $query->limit(20)->get(['id', 'nombres', 'apellidos'])
            ->map(fn (Persona $p) => ['id' => $p->id, 'text' => $p->nombre_completo]);
    }

    // ── Notas de seguimiento ────────────────────────────────────────────

    public function agregarNota(Request $request, Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['required', 'string'],
        ]);

        $persona->notasSeguimiento()->create([
            'user_id' => Auth::id(),
            'fecha' => $request->fecha,
            'nota' => $request->nota,
        ]);

        return back()->with('success', 'Nota registrada correctamente');
    }

    public function destroyNota(NotaSeguimiento $nota_seguimiento)
    {
        $persona = $nota_seguimiento->persona;
        $this->assertDentroDeAlcance($persona);

        $nota_seguimiento->delete();

        return redirect()->route('personas.show', $persona)->with('success', 'Nota eliminada correctamente');
    }

    // ── Procesos de formación ───────────────────────────────────────────

    public function agregarProceso(Request $request, Persona $persona)
    {
        $this->assertDentroDeAlcance($persona);

        $request->validate([
            'proceso_id' => ['required', 'exists:procesos,id'],
            'estado_participacion' => ['required', 'in:en_curso,terminado,incompleto,retirado'],
        ]);

        $persona->procesoParticipaciones()->create([
            'proceso_id' => $request->proceso_id,
            'estado_participacion' => $request->estado_participacion,
            'red_id' => $persona->red_id,
        ]);

        return back()->with('success', 'Proceso agregado correctamente');
    }

    private function assertDentroDeAlcance(Persona $persona): void
    {
        $alcanceIds = Auth::user()->alcancePersonaIds();

        if ($alcanceIds !== null && ! in_array($persona->id, $alcanceIds, true)) {
            abort(403);
        }
    }
}
