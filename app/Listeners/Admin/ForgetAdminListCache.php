<?php

namespace App\Listeners\Admin;

use App\Cache\AdminCache;
use App\Events\Admin\AdminCreated;
use App\Events\Admin\AdminDeleted;
use App\Events\Admin\AdminForceDeleted;
use App\Events\Admin\AdminRestored;
use App\Events\Admin\AdminUpdated;

class ForgetAdminListCache
{
    public function __construct(
        private AdminCache $adminCache
    ) {}

    public function handle(AdminCreated|AdminUpdated|AdminDeleted|AdminRestored|AdminForceDeleted $event): void
    {
        $this->adminCache->forgetList();
    }
}
