<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\TelescopeServiceProvider;
use App\Providers\TwilioServiceProvider;
use SocialiteProviders\Manager\ServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    TelescopeServiceProvider::class,
    TwilioServiceProvider::class,
    ServiceProvider::class,
];
