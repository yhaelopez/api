<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class UserService
{
    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = $this->getListCacheKey($page, $perPage);

        return Cache::tags(['users', 'list'])->remember($cacheKey, 300, function () use ($page, $perPage) {
            return User::paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Get a single user with caching
     */
    public function getUser(int $id): User
    {
        $cacheKey = $this->getSingleUserCacheKey($id);

        return Cache::tags(['users', 'show'])->remember($cacheKey, 300, function () use ($id) {
            return User::findOrFail($id);
        });
    }

    /**
     * Build cache key for users list
     */
    private function getListCacheKey(int $page, int $perPage): string
    {
        return "users:list:{$page}:{$perPage}";
    }

    /**
     * Build cache key for single user
     */
    private function getSingleUserCacheKey(int $id): string
    {
        return "users:show:{$id}";
    }

    /**
     * Clear all user-related cache
     */
    public function clearCache(): void
    {
        Cache::tags(['users'])->flush();
    }
}
