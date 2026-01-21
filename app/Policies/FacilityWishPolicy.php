<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\FacilityWish;
use App\Models\User;

class FacilityWishPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any FacilityWish');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('view FacilityWish');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create FacilityWish');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('update FacilityWish');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('delete FacilityWish');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any FacilityWish');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('restore FacilityWish');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any FacilityWish');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('replicate FacilityWish');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder FacilityWish');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FacilityWish $facilitywish): bool
    {
        return $user->checkPermissionTo('force-delete FacilityWish');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any FacilityWish');
    }
}
