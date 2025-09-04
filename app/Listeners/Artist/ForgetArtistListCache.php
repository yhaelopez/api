<?php

namespace App\Listeners\Artist;

use App\Cache\ArtistCache;
use App\Events\Artist\ArtistCreated;
use App\Events\Artist\ArtistDeleted;
use App\Events\Artist\ArtistForceDeleted;
use App\Events\Artist\ArtistRestored;
use App\Events\Artist\ArtistUpdated;

class ForgetArtistListCache
{
    public function __construct(
        private ArtistCache $artistCache
    ) {}

    public function handle(ArtistCreated|ArtistUpdated|ArtistDeleted|ArtistRestored|ArtistForceDeleted $event): void
    {
        $this->artistCache->forgetList();
    }
}
