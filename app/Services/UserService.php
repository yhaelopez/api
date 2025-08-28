<?php

namespace App\Services;

use App\Cache\UserCache;
use App\Exceptions\ForceDeleteActiveRecordException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private UserCache $userCache,
        private UserRepository $userRepository
    ) {}

    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userCache->rememberList($page, $perPage, function () use ($page, $perPage) {
            return $this->userRepository->paginate($page, $perPage);
        });
    }

    /**
     * Get a single user with caching
     */
    public function getUser(int $id): User
    {
        return $this->userCache->remember($id, function () use ($id) {
            return $this->userRepository->findWithRoles($id);
        });
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restoreUser(User $user): User
    {
        $this->userRepository->restore($user);

        return $user->fresh();
    }

    /**
     * Force delete a user permanently
     *
     * @throws ForceDeleteActiveRecordException When attempting to force delete an active user
     */
    public function forceDeleteUser(User $user): bool
    {
        // Check if user is soft-deleted before force deleting
        if (! $user->trashed()) {
            throw new ForceDeleteActiveRecordException(
                modelClass: User::class,
                modelId: $user->id
            );
        }

        return $this->userRepository->forceDelete($user);
    }
}
