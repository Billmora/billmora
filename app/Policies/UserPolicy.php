<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the authenticated user can edit the given target user.
     *
     * @param  \App\Models\User  $authUser   The currently authenticated user.
     * @param  \App\Models\User  $targetUser The user to be edited.
     * @return bool
     */
    public function update(User $authUser, User $targetUser): bool
    {
        if ($targetUser->isRootAdmin() && !$authUser->isRootAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the authenticated user can delete the given target user.
     *
     * @param  \App\Models\User  $authUser   The currently authenticated user.
     * @param  \App\Models\User  $targetUser The user to be deleted.
     * @return bool
     */
    public function delete(User $authUser, User $targetUser)
    {
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        if ($targetUser->isRootAdmin() && ! $authUser->isRootAdmin()) {
            return false;
        }

        return true;
    }
}
