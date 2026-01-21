<?php

namespace App\Policies;

use App\Models\FacilityRegistrationRequest;
use App\Models\User;
use App\Settings\RegistrationSettings;

class FacilityRegistrationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('student');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        return $facilityregistrationrequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        if (! $this->isFacilityRegistrationOpen()) {
            return false;
        }

        return $user->track_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        if (! $this->isFacilityRegistrationOpen()) {
            return false;
        }

        return $facilityregistrationrequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        if (! $this->isFacilityRegistrationOpen()) {
            return false;
        }

        return $facilityregistrationrequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        return $user->checkPermissionTo('restore FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        return $user->checkPermissionTo('replicate FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FacilityRegistrationRequest $facilityregistrationrequest): bool
    {
        return $user->checkPermissionTo('force-delete FacilityRegistrationRequest');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any FacilityRegistrationRequest');
    }

    private function isFacilityRegistrationOpen(): bool
    {
        return app(RegistrationSettings::class)->facility_registration_open;
    }
}
