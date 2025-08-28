<?php

namespace App\Providers;

use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserForceDeleted;
use App\Events\User\UserRestored;
use App\Events\User\UserUpdated;
use App\Listeners\User\ForgetUserCache;
use App\Listeners\User\ForgetUserListCache;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreated::class => [
            ForgetUserListCache::class,
        ],
        UserUpdated::class => [
            ForgetUserCache::class,
        ],
        UserDeleted::class => [
            ForgetUserCache::class,
            ForgetUserListCache::class,
        ],
        UserRestored::class => [
            ForgetUserCache::class,
            ForgetUserListCache::class,
        ],
        UserForceDeleted::class => [
            ForgetUserCache::class,
            ForgetUserListCache::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
