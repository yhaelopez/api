<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Admin;
use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user, Artist $artist): bool
    {
        // Users can view their own artists
        if ($user instanceof User && $user->id === $artist->owner_id) {
            return true;
        }

        // Admins can view any artist if they have permission
        if ($user instanceof Admin) {
            return $user->hasPermissionTo(PermissionsEnum::ARTISTS_VIEW->value);
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, Artist $artist): bool
    {
        // Users can update their own artists
        if ($user instanceof User && $user->id === $artist->owner_id) {
            return true;
        }

        // Admins can update any artist if they have permission
        if ($user instanceof Admin) {
            return $user->hasPermissionTo(PermissionsEnum::ARTISTS_UPDATE->value);
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, Artist $artist): bool
    {
        // Users can delete their own artists
        if ($user instanceof User && $user->id === $artist->owner_id) {
            return true;
        }

        // Admins can delete any artist if they have permission
        if ($user instanceof Admin) {
            return $user->hasPermissionTo(PermissionsEnum::ARTISTS_DELETE->value);
        }

        // Regular users need permission to delete other artists
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin|User $user, Artist $artist): bool
    {
        // Users can restore their own artists
        if ($user instanceof User && $user->id === $artist->owner_id) {
            return true;
        }

        // Admins can restore any artist if they have permission
        if ($user instanceof Admin) {
            return $user->hasPermissionTo(PermissionsEnum::ARTISTS_RESTORE->value);
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin|User $user, Artist $artist): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_FORCE_DELETE->value);
    }
}
