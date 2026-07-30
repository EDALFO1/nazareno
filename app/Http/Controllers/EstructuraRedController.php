<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\PuntoConexion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstructuraRedController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Estructura de red';
        $alcanceIds = Auth::user()->alcancePersonaIds();
        $esVistaPropia = $alcanceIds !== null;

        $liderId = $esVistaPropia
            ? Auth::user()->persona?->id
            : ($request->integer('lider') ?: null);

        $opcionesLideres = $esVistaPropia ? collect() : $this->opcionesLideres();
        $estructura = $this->construirEstructura($liderId, $alcanceIds);

        return view('modules.estructura_red.index', compact('titulo', 'esVistaPropia', 'liderId', 'opcionesLideres', 'estructura'));
    }

    /**
     * @return array<int, string>
     */
    private function opcionesLideres(): array
    {
        $idsConDiscipulos = Persona::query()->whereNotNull('lider_id')->pluck('lider_id')->unique();

        return Persona::query()
            ->where(function (Builder $query) use ($idsConDiscipulos) {
                $query->whereIn('id', $idsConDiscipulos)
                    ->orWhere(fn (Builder $q) => $q->whereNotNull('red_id')->whereNull('lider_id'));
            })
            ->with('red')
            ->orderBy('nombres')
            ->get()
            ->mapWithKeys(fn (Persona $persona) => [
                $persona->id => "{$persona->nombre_completo} — {$persona->red?->nombre}",
            ])
            ->all();
    }

    /**
     * @param  array<int>|null  $alcanceIds
     * @return array{lider: Persona, arbol: array, puntos: \Illuminate\Support\Collection, resumen: array{personas: int, lideres: int, puntos: int}}|null
     */
    private function construirEstructura(?int $liderId, ?array $alcanceIds): ?array
    {
        if (! $liderId) {
            return null;
        }

        if ($alcanceIds !== null && ! in_array($liderId, $alcanceIds, true)) {
            return null;
        }

        $lider = Persona::with('red')->find($liderId);

        if (! $lider) {
            return null;
        }

        $ids = $lider->subarbolIds();

        $personas = Persona::whereIn('id', $ids)->orderBy('nombres')->get();

        $porLider = $personas->groupBy('lider_id');

        $construir = function (Persona $persona) use (&$construir, $porLider) {
            return [
                'persona' => $persona,
                'hijos' => ($porLider->get($persona->id) ?? collect())
                    ->map($construir)
                    ->values()
                    ->all(),
            ];
        };

        $idsConDiscipulos = $personas->pluck('lider_id')->filter()->unique();
        $totalLideres = $idsConDiscipulos->count() + ($idsConDiscipulos->contains($lider->id) ? 0 : 1);

        $puntos = PuntoConexion::whereIn('lider_id', $ids)
            ->with(['lider', 'anfitrion'])
            ->orderBy('nombre')
            ->get();

        return [
            'lider' => $lider,
            'arbol' => $construir($lider),
            'puntos' => $puntos,
            'resumen' => [
                'personas' => $personas->count(),
                'lideres' => $totalLideres,
                'puntos' => $puntos->count(),
            ],
        ];
    }
}
