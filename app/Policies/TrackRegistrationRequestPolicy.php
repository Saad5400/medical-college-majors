<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TrackRegistrationRequest;
use App\Models\User;

class TrackRegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('view TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('update TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('delete TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('restore TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('replicate TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TrackRegistrationRequest $trackregistrationrequest): bool
    {
        return $user->checkPermissionTo('force-delete TrackRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TrackRegistrationRequest');
    }
}
