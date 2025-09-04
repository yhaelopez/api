<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Artist $artist): bool
    {
        // Users can view their own artists
        if ($user->id === $artist->owner_id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Artist $artist): bool
    {
        // Users can update their own artists
        if ($user->id === $artist->owner_id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Artist $artist): bool
    {
        // Users can delete their own artists
        if ($user->id === $artist->owner_id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Artist $artist): bool
    {
        // Users can restore their own artists
        if ($user->id === $artist->owner_id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Artist $artist): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ARTISTS_FORCE_DELETE->value);
    }
}
