<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TrackSpecialization;
use App\Models\User;

class TrackSpecializationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TrackSpecialization');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('view TrackSpecialization');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TrackSpecialization');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('update TrackSpecialization');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('delete TrackSpecialization');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TrackSpecialization');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('restore TrackSpecialization');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TrackSpecialization');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('replicate TrackSpecialization');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TrackSpecialization');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TrackSpecialization $trackspecialization): bool
    {
        return $user->checkPermissionTo('force-delete TrackSpecialization');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TrackSpecialization');
    }
}
