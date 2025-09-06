<?php

namespace App\Events\Admin;

use App\Models\Admin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Admin $admin
    ) {}
}
