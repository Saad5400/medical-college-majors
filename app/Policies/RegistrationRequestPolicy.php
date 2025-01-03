<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\RegistrationRequest;
use App\Models\User;

class RegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any RegistrationRequest');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('view RegistrationRequest');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create RegistrationRequest');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('update RegistrationRequest');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('delete RegistrationRequest');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any RegistrationRequest');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('restore RegistrationRequest');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any RegistrationRequest');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('replicate RegistrationRequest');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder RegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RegistrationRequest $registrationrequest): bool
    {
        return $user->checkPermissionTo('force-delete RegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any RegistrationRequest');
    }
}
