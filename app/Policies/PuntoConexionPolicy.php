<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PuntoConexion;
use Illuminate\Auth\Access\HandlesAuthorization;

class PuntoConexionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_punto::conexion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('view_punto::conexion') && $this->dentroDelAlcance($user, $puntoConexion);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_punto::conexion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('update_punto::conexion') && $this->dentroDelAlcance($user, $puntoConexion);
    }

    /**
     * Un líder de red solo puede ver/editar puntos de conexión cuyo líder esté
     * dentro de su propio subárbol de discipulado.
     */
    protected function dentroDelAlcance(User $user, PuntoConexion $puntoConexion): bool
    {
        $alcanceIds = $user->alcancePersonaIds();

        if ($alcanceIds === null) {
            return true;
        }

        return in_array($puntoConexion->lider_id, $alcanceIds, true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('delete_punto::conexion');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_punto::conexion');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('force_delete_punto::conexion');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_punto::conexion');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('restore_punto::conexion');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_punto::conexion');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PuntoConexion $puntoConexion): bool
    {
        return $user->can('replicate_punto::conexion');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_punto::conexion');
    }
}
