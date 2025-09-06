<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Admin;

class AdminPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Admin $model): bool
    {
        // Admins can view their own profile
        if ($admin->id === $model->id) {
            return true;
        }

        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Admin $model): bool
    {
        // Admins can update their own profile
        if ($admin->id === $model->id) {
            return true;
        }

        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Admin $model): bool
    {
        // Prevent admins from deleting themselves via API (but allow other admins to delete anyone)
        if ($admin->id === $model->id) {
            return false;
        }

        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_FORCE_DELETE->value);
    }

    /**
     * Determine whether the user can send password reset links.
     */
    public function sendPasswordResetLink(Admin $admin, Admin $model): bool
    {
        // Admins can send password reset links to themselves
        if ($admin->id === $model->id) {
            return true;
        }

        // Admins with update permission can send password reset links to others
        return $admin->hasPermissionTo(PermissionsEnum::ADMINS_UPDATE->value);
    }
}
