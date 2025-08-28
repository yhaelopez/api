<?php

namespace App\Listeners\User;

use App\Cache\UserCache;

class ForgetUserCache
{
    public function __construct(
        private UserCache $userCache
    ) {}

    public function handle($event): void
    {
        $this->userCache->forget($event->user->id);
    }
}
