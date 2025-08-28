<?php

namespace App\Observers;

use App\Cache\UserCache;
use App\Models\User;

class UserObserver
{
    public function __construct(
        private UserCache $userCache
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // When a user is created, we need to clear list caches
        // as the pagination results will change
        $this->userCache->forgetList();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // When a user is updated, only clear that specific user's cache
        $this->userCache->forget($user->id);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // When a user is deleted, clear list caches and the specific user cache
        $this->userCache->forgetList();
        $this->userCache->forget($user->id);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // When a user is restored, clear list caches and the specific user cache
        $this->userCache->forgetList();
        $this->userCache->forget($user->id);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // When a user is force deleted, clear list caches and the specific user cache
        $this->userCache->forgetList();
        $this->userCache->forget($user->id);
    }
}
