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
     * Find a user with trashed
     */
    public function findWithTrashed(int $id): User
    {
        return User::withTrashed()->findOrFail($id);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
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
        $user = User::create($data);

        // Load the roles relationship for immediate use
        return $user->load('roles');
    }

    /**
     * Update an existing user
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        // Load the roles relationship for immediate use
        return $user->fresh(['roles']);
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
     * Restore a soft-deleted user and return it with relationships loaded
     */
    public function restoreWithRoles(User $user): User
    {
        $user->restore();

        // Load the roles relationship for immediate use
        return $user->fresh(['roles']);
    }

    /**
     * Force delete a user
     */
    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }
}
