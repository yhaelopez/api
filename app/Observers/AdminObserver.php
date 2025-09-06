<?php

namespace App\Observers;

use App\Events\Admin\AdminCreated;
use App\Events\Admin\AdminDeleted;
use App\Events\Admin\AdminForceDeleted;
use App\Events\Admin\AdminRestored;
use App\Events\Admin\AdminUpdated;
use App\Models\Admin;

class AdminObserver
{
    public function created(Admin $admin): void
    {
        event(new AdminCreated($admin));
    }

    public function updated(Admin $admin): void
    {
        event(new AdminUpdated($admin));
    }

    public function deleted(Admin $admin): void
    {
        event(new AdminDeleted($admin));
    }

    public function restored(Admin $admin): void
    {
        event(new AdminRestored($admin));
    }

    public function forceDeleted(Admin $admin): void
    {
        event(new AdminForceDeleted($admin));
    }
}
