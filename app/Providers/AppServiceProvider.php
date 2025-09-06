<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Artist;
use App\Models\User;
use App\Observers\AdminObserver;
use App\Observers\ArtistObserver;
use App\Observers\UserObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Admin::observe(AdminObserver::class);
        User::observe(UserObserver::class);
        Artist::observe(ArtistObserver::class);
    }
}
