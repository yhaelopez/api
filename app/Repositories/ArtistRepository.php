<?php

namespace App\Repositories;

use App\Models\Artist;
use App\Services\FilterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArtistRepository
{
    public function __construct(
        private FilterService $filterService
    ) {}

    /**
     * Find an artist by ID
     */
    public function find(int $id): ?Artist
    {
        return Artist::find($id);
    }

    /**
     * Find an artist by ID or throw exception
     */
    public function findOrFail(int $id): Artist
    {
        return Artist::findOrFail($id);
    }

    /**
     * Find an artist with owner relationship loaded
     */
    public function findWithOwner(int $id): Artist
    {
        return Artist::with(['owner'])->findOrFail($id);
    }

    /**
     * Find an artist with trashed
     */
    public function findWithTrashed(int $id): Artist
    {
        return Artist::withTrashed()->findOrFail($id);
    }

    /**
     * Get paginated list of artists with owner and filters
     */
    public function paginate(int $page, int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Artist::with(['owner']);

        $this->filterService->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new artist
     */
    public function create(array $data): Artist
    {
        $artist = Artist::create($data);

        // Load the owner relationship for immediate use
        return $artist->load('owner');
    }

    /**
     * Update an existing artist
     */
    public function update(Artist $artist, array $data): Artist
    {
        $artist->update($data);

        // Load the owner relationship for immediate use
        return $artist->fresh(['owner']);
    }

    /**
     * Delete an artist (soft delete)
     */
    public function delete(Artist $artist): bool
    {
        return $artist->delete();
    }

    /**
     * Restore a soft-deleted artist
     */
    public function restore(Artist $artist): bool
    {
        return $artist->restore();
    }

    /**
     * Restore a soft-deleted artist and return it with relationships loaded
     */
    public function restoreWithOwner(Artist $artist): Artist
    {
        $artist->restore();

        // Load the owner relationship for immediate use
        return $artist->fresh(['owner']);
    }

    /**
     * Force delete an artist
     */
    public function forceDelete(Artist $artist): bool
    {
        return $artist->forceDelete();
    }
}
