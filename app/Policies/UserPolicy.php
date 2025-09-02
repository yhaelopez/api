<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::USERS_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::USERS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::USERS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::USERS_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Prevent users from deleting themselves via API
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo(PermissionsEnum::USERS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::USERS_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::USERS_FORCE_DELETE->value);
    }

    /**
     * Determine whether the user can send password reset links.
     */
    public function sendPasswordResetLink(User $user, User $model): bool
    {
        // Users can send password reset links to themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Users with update permission can send password reset links to others
        return $user->hasPermissionTo(PermissionsEnum::USERS_UPDATE->value);
    }
}
