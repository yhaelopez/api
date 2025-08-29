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
        private UserRepository $userRepository,
        private LoggerService $logger
    ) {}

    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $this->logger->user()->info('Users list retrieved', [
            'page' => $page,
            'per_page' => $perPage,
            'action' => 'list_users'
        ]);

        return $this->userCache->rememberList($page, $perPage, function () use ($page, $perPage) {
            return $this->userRepository->paginate($page, $perPage);
        });
    }

    /**
     * Get a single user with caching
     */
    public function getUser(int $id): User
    {
        $this->logger->user()->info('User retrieved', [
            'user_id' => $id,
            'action' => 'get_user'
        ]);

        return $this->userCache->remember($id, function () use ($id) {
            return $this->userRepository->findWithRoles($id);
        });
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $this->logger->user()->info('User created', [
            'action' => 'create_user',
            'user_data' => array_intersect_key($data, array_flip(['name', 'email', 'role']))
        ]);

        $user = $this->userRepository->create($data);

        $this->logger->user()->info('User created successfully', [
            'user_id' => $user->id,
            'action' => 'user_created_success'
        ]);

        return $user;
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        $this->logger->user()->info('User updated', [
            'user_id' => $user->id,
            'action' => 'update_user',
            'updated_fields' => array_keys($data)
        ]);

        $updatedUser = $this->userRepository->update($user, $data);

        $this->logger->user()->info('User updated successfully', [
            'user_id' => $user->id,
            'action' => 'user_updated_success'
        ]);

        return $updatedUser;
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        $this->logger->user()->warning('User deleted (soft delete)', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'delete_user'
        ]);

        $deleted = $this->userRepository->delete($user);

        if ($deleted) {
            $this->logger->user()->info('User soft deleted successfully', [
                'user_id' => $user->id,
                'action' => 'user_soft_deleted_success'
            ]);
        } else {
            $this->logger->user()->error('Failed to soft delete user', [
                'user_id' => $user->id,
                'action' => 'user_soft_delete_failed'
            ]);
        }

        return $deleted;
    }

    /**
     * Restore a soft-deleted user
     */
    public function restoreUser(User $user): User
    {
        $this->logger->user()->info('User restored', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'restore_user'
        ]);

        $this->userRepository->restore($user);

        $this->logger->user()->info('User restored successfully', [
            'user_id' => $user->id,
            'action' => 'user_restored_success'
        ]);

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
        if (!$user->trashed()) {
            $this->logger->user()->error('Attempted to force delete active user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => 'force_delete_active_user_attempt'
            ]);

            throw new ForceDeleteActiveRecordException(
                modelClass: User::class,
                modelId: $user->id
            );
        }

        $this->logger->user()->warning('User force deleted permanently', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'force_delete_user'
        ]);

        $deleted = $this->userRepository->forceDelete($user);

        if ($deleted) {
            $this->logger->user()->info('User permanently deleted successfully', [
                'user_id' => $user->id,
                'action' => 'user_permanently_deleted_success'
            ]);
        } else {
            $this->logger->user()->error('Failed to permanently delete user', [
                'user_id' => $user->id,
                'action' => 'user_permanent_delete_failed'
            ]);
        }

        return $deleted;
    }
}
