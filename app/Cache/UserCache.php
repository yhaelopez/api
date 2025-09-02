<?php

namespace App\Cache;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class UserCache
{
    /**
     * Get a user from cache or store it if not found
     */
    public function remember(int $id, callable $callback): User
    {
        $cacheKey = $this->getSingleUserCacheKey($id);

        return Cache::tags(['users', 'show'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Get a paginated list of users from cache or store it if not found
     */
    public function rememberList(int $page, int $perPage, array $filters, callable $callback): LengthAwarePaginator
    {
        $cacheKey = $this->getListCacheKey($page, $perPage, $filters);

        return Cache::tags(['users', 'list'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Clear cache for a specific user
     */
    public function forget(int $userId): void
    {
        $cacheKey = $this->getSingleUserCacheKey($userId);
        Cache::tags(['users', 'show'])->forget($cacheKey);
    }

    /**
     * Clear all list caches
     */
    public function forgetList(): void
    {
        Cache::tags(['users', 'list'])->flush();
    }

    /**
     * Clear all user-related cache
     */
    public function flush(): void
    {
        Cache::tags(['users'])->flush();
    }

    /**
     * Build cache key for users list
     */
    private function getListCacheKey(int $page, int $perPage, array $filters = []): string
    {
        $filterString = '';
        if (! empty($filters)) {
            $filterString = ':'.http_build_query($filters, '', ':');
        }

        return "users:list:{$page}:{$perPage}{$filterString}";
    }

    /**
     * Build cache key for single user
     */
    private function getSingleUserCacheKey(int $id): string
    {
        return "users:show:{$id}";
    }
}
