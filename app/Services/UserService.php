<?php

namespace App\Services;

use App\Cache\UserCache;
use App\Exceptions\ForceDeleteActiveRecordException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserCache $userCache,
        private UserRepository $userRepository,
        private LoggerService $logger,
        private RoleService $roleService,
        private StorageService $storageService
    ) {}

    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userCache->rememberList($page, $perPage, function () use ($page, $perPage, $filters) {
            return $this->userRepository->paginate($page, $perPage, $filters);
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
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        // Assign role if provided
        if (isset($data['role_id'])) {
            $role = $this->roleService->findRole($data['role_id']);
            if ($role) {
                $this->roleService->assignRole($user, $role);
            }
        }

        $this->logger->user()->info('User created', [
            'user_id' => $user->id,
            'action' => 'user_created_success',
        ]);

        return $user;
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        // Handle password hashing if provided (leave blank to keep current password)
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if empty to avoid updating it
            unset($data['password']);
        }

        $updatedUser = $this->userRepository->update($user, $data);

        // Update role if provided
        if (! empty($data['role_id'])) {
            $role = $this->roleService->findRole($data['role_id']);

            // Remove existing roles and assign new one through service
            $this->roleService->syncRoles($updatedUser, [$role]);
        }

        $this->logger->user()->info('User updated', [
            'user_id' => $user->id,
            'action' => 'user_updated_success',
        ]);

        return $updatedUser;
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser(User $user): bool
    {

        $this->userRepository->delete($user);

        $this->logger->user()->info('User soft deleted', [
            'user_id' => $user->id,
            'action' => 'user_soft_deleted_success',
        ]);

        return true;
    }

    /**
     * Add profile photo to existing user
     */
    public function addProfilePhoto(User $user, UploadedFile $profilePhoto): void
    {
        // Clear existing profile photo (single file collection)
        $user->clearMediaCollection('profile_photos');

        // Add new profile photo
        $user->addMedia(
            file: $profilePhoto
        )
            ->usingFileName(
                fileName: $this->storageService->generateProfilePhotoFilename($profilePhoto)
            )
            ->toMediaCollection(
                collectionName: 'profile_photos',
                diskName: $this->storageService->getProfilePhotoDisk()
            );

        $this->logger->user()->info('Profile photo added to user', [
            'user_id' => $user->id,
            'filename' => $profilePhoto->getClientOriginalName(),
            'action' => 'profile_photo_added',
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restoreUser(User $user): User
    {
        $this->userRepository->restore($user);

        $this->logger->user()->info('User restored', [
            'user_id' => $user->id,
            'action' => 'user_restored_success',
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
        if (! $user->trashed()) {
            $this->logger->user()->alert('Attempted to force delete active user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => 'force_delete_active_user_attempt',
            ]);

            throw new ForceDeleteActiveRecordException(
                modelClass: User::class,
                modelId: $user->id
            );
        }

        $this->userRepository->forceDelete($user);

        $this->logger->user()->info('User permanently deleted', [
            'user_id' => $user->id,
            'action' => 'user_permanently_deleted_success',
        ]);

        return true;
    }
}
