<?php

namespace App\Cache;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AdminCache
{
    /**
     * Get an admin from cache or store it if not found
     */
    public function remember(int $id, callable $callback): Admin
    {
        $cacheKey = $this->getSingleAdminCacheKey($id);

        return Cache::tags(['admins', 'show'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Get a paginated list of admins from cache or store it if not found
     */
    public function rememberList(int $page, int $perPage, array $filters, callable $callback): LengthAwarePaginator
    {
        $cacheKey = $this->getListCacheKey($page, $perPage, $filters);

        return Cache::tags(['admins', 'list'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Clear cache for a specific admin
     */
    public function forget(int $adminId): void
    {
        $cacheKey = $this->getSingleAdminCacheKey($adminId);
        Cache::tags(['admins', 'show'])->forget($cacheKey);
    }

    /**
     * Clear all list caches
     */
    public function forgetList(): void
    {
        Cache::tags(['admins', 'list'])->flush();
    }

    /**
     * Clear all admin-related cache
     */
    public function flush(): void
    {
        Cache::tags(['admins'])->flush();
    }

    /**
     * Build cache key for admins list
     */
    private function getListCacheKey(int $page, int $perPage, array $filters = []): string
    {
        $filterString = '';
        if (! empty($filters)) {
            $filterString = ':'.http_build_query($filters, '', ':');
        }

        return "admins:list:{$page}:{$perPage}{$filterString}";
    }

    /**
     * Build cache key for single admin
     */
    private function getSingleAdminCacheKey(int $id): string
    {
        return "admins:show:{$id}";
    }
}
