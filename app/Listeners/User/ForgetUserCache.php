<?php

namespace App\Listeners\User;

use App\Cache\UserCache;
use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserForceDeleted;
use App\Events\User\UserRestored;
use App\Events\User\UserUpdated;

class ForgetUserCache
{
    public function __construct(
        private UserCache $userCache
    ) {}

    public function handle(UserCreated|UserUpdated|UserDeleted|UserRestored|UserForceDeleted $event): void
    {
        $this->userCache->forget($event->user->id);
    }
}
