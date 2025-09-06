<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::USERS_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, User $model): bool
    {
        // Users can view their own profile
        if ($admin->id === $model->id) {
            return true;
        }

        return $admin->hasPermissionTo(PermissionsEnum::USERS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::USERS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, User $model): bool
    {
        // Users can update their own profile
        if ($admin->id === $model->id) {
            return true;
        }

        return $admin->hasPermissionTo(PermissionsEnum::USERS_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, User $model): bool
    {
        // Prevent users from deleting themselves via API (but allow admins to delete anyone)
        if ($admin->id === $model->id) {
            return false;
        }

        return $admin->hasPermissionTo(PermissionsEnum::USERS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, User $model): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::USERS_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, User $model): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::USERS_FORCE_DELETE->value);
    }

    /**
     * Determine whether the user can send password reset links.
     */
    public function sendPasswordResetLink(Admin $admin, User $model): bool
    {
        // Users can send password reset links to themselves
        if ($admin->id === $model->id) {
            return true;
        }

        // Users with update permission can send password reset links to others
        return $admin->hasPermissionTo(PermissionsEnum::USERS_UPDATE->value);
    }
}
