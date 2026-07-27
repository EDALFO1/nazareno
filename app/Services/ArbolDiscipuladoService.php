<?php

namespace App\Services;

use App\Models\Persona;
use Illuminate\Support\Facades\DB;

/**
 * Cálculos sobre el árbol de discipulado (quién lidera a quién).
 */
class ArbolDiscipuladoService
{
    /**
     * IDs de todos los descendientes de una persona en el árbol de
     * discipulado (cualquier profundidad), usando una CTE recursiva de
     * MySQL. No incluye el propio id.
     *
     * @return array<int>
     */
    public function descendientesIds(Persona $persona): array
    {
        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE arbol AS (
                SELECT id FROM personas WHERE lider_id = ?
                UNION ALL
                SELECT p.id FROM personas p
                INNER JOIN arbol a ON p.lider_id = a.id
            )
            SELECT id FROM arbol
        SQL, [$persona->id]);

        return array_map(fn ($row) => $row->id, $rows);
    }

    /**
     * IDs de la persona más todos sus descendientes — útil para scoping de acceso.
     *
     * @return array<int>
     */
    public function subarbolIds(Persona $persona): array
    {
        return [$persona->id, ...$this->descendientesIds($persona)];
    }
}
