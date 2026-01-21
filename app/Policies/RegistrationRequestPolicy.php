<?php

namespace App\Policies;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Settings\RegistrationSettings;

class RegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegistrationRequest $registrationrequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $registrationrequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('leader')) {
            return false;
        }

        $settings = app(RegistrationSettings::class);
        if (!$settings->track_registration_open) {
            return false;
        }

        return $user->registrationRequests()->doesntExist();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationRequest $record): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('leader')) {
            return false;
        }

        $settings = app(RegistrationSettings::class);
        if (!$settings->track_registration_open) {
            return false;
        }

        return $record->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationRequest $registrationrequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $settings = app(RegistrationSettings::class);
        if (!$settings->track_registration_open) {
            return false;
        }

        return $registrationrequest->user_id === $user->id;
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
