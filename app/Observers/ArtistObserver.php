<?php

namespace App\Observers;

use App\Events\Artist\ArtistCreated;
use App\Events\Artist\ArtistDeleted;
use App\Events\Artist\ArtistForceDeleted;
use App\Events\Artist\ArtistRestored;
use App\Events\Artist\ArtistUpdated;
use App\Models\Artist;

class ArtistObserver
{
    public function created(Artist $artist): void
    {
        event(new ArtistCreated($artist));
    }

    public function updated(Artist $artist): void
    {
        event(new ArtistUpdated($artist));
    }

    public function deleted(Artist $artist): void
    {
        event(new ArtistDeleted($artist));
    }

    public function restored(Artist $artist): void
    {
        event(new ArtistRestored($artist));
    }

    public function forceDeleted(Artist $artist): void
    {
        event(new ArtistForceDeleted($artist));
    }
}
