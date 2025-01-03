<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\MajorRegistrationRequest;
use App\Models\User;

class MajorRegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('view MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('update MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('delete MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('restore MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('replicate MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MajorRegistrationRequest $majorregistrationrequest): bool
    {
        return $user->checkPermissionTo('force-delete MajorRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any MajorRegistrationRequest');
    }
}
