<?php

namespace App\Cache;

class CacheManager
{
    public function __construct(
        private UserCache $userCache
    ) {}

    /**
     * Clear all caches across all models
     */
    public function flushAll(): void
    {
        $this->userCache->flush();
        // Add other model caches here as they are created
    }
}
