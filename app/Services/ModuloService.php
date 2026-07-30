<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class ModuloService
{
    public function puedeAcceder(string $slug): bool
    {
        return $this->modulosPermitidos()->contains($slug);
    }

    /**
     * Slugs de los módulos a los que tiene acceso el usuario autenticado,
     * según su rol. Usado también para armar el menú lateral.
     */
    public function modulosPermitidos(): Collection
    {
        $user = Auth::user();

        if (! $user || ! $user->rol) {
            return collect();
        }

        return $user->rol->modulos()->pluck('slug');
    }
}
