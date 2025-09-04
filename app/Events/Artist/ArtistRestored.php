<?php

namespace App\Events\Artist;

use App\Models\Artist;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArtistRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Artist $artist
    ) {}
}
