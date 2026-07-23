<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Persona;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_persona');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Persona $persona): bool
    {
        return $user->can('view_persona') && $this->dentroDelAlcance($user, $persona);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_persona');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Persona $persona): bool
    {
        return $user->can('update_persona') && $this->dentroDelAlcance($user, $persona);
    }

    /**
     * Un líder de red solo puede ver/editar personas dentro de su propio subárbol
     * de discipulado. Admin Principal y Admin General no tienen esta restricción.
     */
    protected function dentroDelAlcance(User $user, Persona $persona): bool
    {
        if (! $user->hasRole('lider_red') || $user->hasAnyRole(['super_admin', 'admin_general'])) {
            return true;
        }

        $liderPersona = $user->persona;

        if (! $liderPersona) {
            return false;
        }

        return in_array($persona->id, $liderPersona->subarbolIds(), true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Persona $persona): bool
    {
        return $user->can('delete_persona');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_persona');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Persona $persona): bool
    {
        return $user->can('force_delete_persona');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_persona');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Persona $persona): bool
    {
        return $user->can('restore_persona');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_persona');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Persona $persona): bool
    {
        return $user->can('replicate_persona');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_persona');
    }
}
