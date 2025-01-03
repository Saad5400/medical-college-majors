<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Major');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('view Major');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Major');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('update Major');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('delete Major');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Major');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('restore Major');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Major');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('replicate Major');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Major');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Major $major): bool
    {
        return $user->checkPermissionTo('force-delete Major');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Major');
    }
}
