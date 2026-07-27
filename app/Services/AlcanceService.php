<?php

namespace App\Services;

use App\Models\User;

/**
 * Calcula el "alcance" de un usuario: qué personas puede ver según su rol.
 */
class AlcanceService
{
    /**
     * IDs de personas visibles para este usuario, o null si no tiene
     * restricción (Admin Principal / Admin General ven todo). Un líder de
     * red solo ve su propio subárbol de discipulado.
     *
     * @return array<int>|null
     */
    public function personaIdsVisiblesPara(User $user): ?array
    {
        if (! $user->hasRole('lider_red') || $user->hasAnyRole(['super_admin', 'admin_general'])) {
            return null;
        }

        if (! $user->persona) {
            return [];
        }

        return app(ArbolDiscipuladoService::class)->subarbolIds($user->persona);
    }
}
