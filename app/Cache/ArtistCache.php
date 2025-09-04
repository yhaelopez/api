<?php

namespace App\Cache;

use App\Models\Artist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ArtistCache
{
    /**
     * Get an artist from cache or store it if not found
     */
    public function remember(int $id, callable $callback): Artist
    {
        $cacheKey = $this->getSingleArtistCacheKey($id);

        return Cache::tags(['artists', 'show'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Get a paginated list of artists from cache or store it if not found
     */
    public function rememberList(int $page, int $perPage, array $filters, callable $callback): LengthAwarePaginator
    {
        $cacheKey = $this->getListCacheKey($page, $perPage, $filters);

        return Cache::tags(['artists', 'list'])->remember($cacheKey, 300, $callback);
    }

    /**
     * Clear cache for a specific artist
     */
    public function forget(int $artistId): void
    {
        $cacheKey = $this->getSingleArtistCacheKey($artistId);
        Cache::tags(['artists', 'show'])->forget($cacheKey);
    }

    /**
     * Clear all list caches
     */
    public function forgetList(): void
    {
        Cache::tags(['artists', 'list'])->flush();
    }

    /**
     * Clear all artist-related cache
     */
    public function flush(): void
    {
        Cache::tags(['artists'])->flush();
    }

    /**
     * Build cache key for artists list
     */
    private function getListCacheKey(int $page, int $perPage, array $filters = []): string
    {
        $filterString = '';
        if (! empty($filters)) {
            $filterString = ':'.http_build_query($filters, '', ':');
        }

        return "artists:list:{$page}:{$perPage}{$filterString}";
    }

    /**
     * Build cache key for single artist
     */
    private function getSingleArtistCacheKey(int $id): string
    {
        return "artists:show:{$id}";
    }
}
