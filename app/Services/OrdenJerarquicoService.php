<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Red;

/**
 * Calcula el orden en el que se listan las personas en el panel: agrupadas
 * por red (alfabético), y dentro de cada red, primero el líder principal,
 * luego sus líderes de primera línea, luego los de segunda, etc. (recorrido
 * en profundidad del árbol de discipulado).
 */
class OrdenJerarquicoService
{
    /**
     * @return array<int>
     */
    public function calcular(): array
    {
        $personas = Persona::query()
            ->select('id', 'lider_id', 'red_id', 'nombres')
            ->orderBy('nombres')
            ->get();

        $porLider = $personas->groupBy('lider_id');

        $ordenRedes = Red::orderBy('nombre')->pluck('id');

        $ids = [];

        $agregar = function (Persona $persona) use (&$agregar, &$ids, $porLider) {
            $ids[] = $persona->id;

            foreach (($porLider->get($persona->id) ?? collect())->sortBy('nombres') as $hijo) {
                $agregar($hijo);
            }
        };

        foreach ($ordenRedes as $redId) {
            $lideresPrincipales = $personas
                ->where('red_id', $redId)
                ->whereNull('lider_id')
                ->sortBy('nombres');

            foreach ($lideresPrincipales as $liderPrincipal) {
                $agregar($liderPrincipal);
            }
        }

        $yaOrdenados = collect($ids);

        foreach ($personas->whereNotIn('id', $yaOrdenados)->sortBy('nombres') as $persona) {
            $ids[] = $persona->id;
        }

        return $ids;
    }
}
