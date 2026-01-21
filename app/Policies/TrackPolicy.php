<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Track;
use App\Models\User;

class TrackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Track');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('view Track');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Track');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('update Track');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('delete Track');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Track');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('restore Track');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Track');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('replicate Track');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Track');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Track $track): bool
    {
        return $user->checkPermissionTo('force-delete Track');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Track');
    }
}
