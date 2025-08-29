<?php

namespace App\Repositories;

use App\Models\User;
use App\Services\FilterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(
        private FilterService $filterService
    ) {}

    /**
     * Find a user by ID
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a user by ID or throw exception
     */
    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Find a user with roles and permissions loaded
     */
    public function findWithRoles(int $id): User
    {
        return User::with(['roles', 'permissions'])->findOrFail($id);
    }

    /**
     * Get paginated list of users with roles and filters
     */
    public function paginate(int $page, int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['roles']);

        $this->filterService->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update an existing user
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    /**
     * Delete a user (soft delete)
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore(User $user): bool
    {
        return $user->restore();
    }

    /**
     * Force delete a user
     */
    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }
}
