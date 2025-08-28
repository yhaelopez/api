<?php

namespace App\Observers;

use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserForceDeleted;
use App\Events\User\UserRestored;
use App\Events\User\UserUpdated;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        event(new UserCreated($user));
    }

    public function updated(User $user): void
    {
        event(new UserUpdated($user));
    }

    public function deleted(User $user): void
    {
        event(new UserDeleted($user));
    }

    public function restored(User $user): void
    {
        event(new UserRestored($user));
    }

    public function forceDeleted(User $user): void
    {
        event(new UserForceDeleted($user));
    }
}
