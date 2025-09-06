<?php

namespace App\Listeners\Socialite;

use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\Provider;

class SpotifyExtendSocialite
{
    /**
     * Handle the event.
     */
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite('spotify', Provider::class);
    }
}
