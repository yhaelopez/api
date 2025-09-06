<?php

namespace App\Services;

use App\Cache\UserCache;
use App\Exceptions\ForceDeleteActiveRecordException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserService
{
    public function __construct(
        private UserCache $userCache,
        private UserRepository $userRepository,
        private LoggerService $logger,
        private RoleService $roleService,
        private StorageService $storageService,
        private InAppNotificationService $inAppNotificationService
    ) {}

    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userCache->rememberList($page, $perPage, $filters, function () use ($page, $perPage, $filters) {
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

        $this->logger->users()->info('User created', [
            'user_id' => $user->id,
            'action' => 'user_created_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'User Created',
            "User '{$user->name}' has been created successfully."
        );

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

        $this->logger->users()->info('User updated', [
            'user_id' => $user->id,
            'action' => 'user_updated_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'User Updated',
            "User '{$updatedUser->name}' has been updated successfully."
        );

        return $updatedUser;
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        $userName = $user->name;

        $this->userRepository->delete($user);

        $this->logger->users()->info('User soft deleted', [
            'user_id' => $user->id,
            'action' => 'user_soft_deleted_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'User Deleted',
            "User '{$userName}' has been moved to trash."
        );

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

        $this->logger->users()->info('Profile photo added to user', [
            'user_id' => $user->id,
            'filename' => $profilePhoto->getClientOriginalName(),
            'action' => 'profile_photo_added',
        ]);
    }

    /**
     * Remove profile photo from user
     */
    public function removeProfilePhoto(User $user): bool
    {
        if (! $user->hasMedia('profile_photos')) {
            // Send warning notification to current user
            $this->inAppNotificationService->warning(
                'No Profile Photo',
                'This user does not have a profile photo to remove.'
            );

            return false;
        }

        // Clear the profile photos collection (removes files and database records)
        $user->clearMediaCollection('profile_photos');

        $this->logger->users()->info('Profile photo removed from user', [
            'user_id' => $user->id,
            'action' => 'profile_photo_removed',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Profile Photo Removed',
            "Profile photo for '{$user->name}' has been removed successfully."
        );

        return true;
    }

    /**
     * Restore a soft-deleted user
     */
    public function restoreUser(User $user): User
    {
        $this->userRepository->restore($user);

        $this->logger->users()->info('User restored', [
            'user_id' => $user->id,
            'action' => 'user_restored_success',
        ]);

        $restoredUser = $user->fresh();

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'User Restored',
            "User '{$restoredUser->name}' has been restored successfully."
        );

        return $restoredUser;
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
            $this->logger->users()->alert('Attempted to force delete active user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => 'force_delete_active_user_attempt',
            ]);

            throw new ForceDeleteActiveRecordException(
                modelClass: User::class,
                modelId: $user->id
            );
        }

        $userName = $user->name;
        $this->userRepository->forceDelete($user);

        $this->logger->users()->info('User permanently deleted', [
            'user_id' => $user->id,
            'action' => 'user_permanently_deleted_success',
        ]);

        // Send warning notification to current user
        $this->inAppNotificationService->warning(
            'User Permanently Deleted',
            "User '{$userName}' has been permanently deleted and cannot be recovered."
        );

        return true;
    }

    /**
     * Send password reset link to the user.
     */
    public function sendPasswordResetLink(User $user): bool
    {
        $status = Password::broker('users')->sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->logger->users()->info('Password reset link sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => 'password_reset_link_sent',
            ]);

            $this->inAppNotificationService->success(
                'Password Reset Link Sent',
                "Password reset link has been sent to {$user->email}."
            );

            return true;
        }

        $this->logger->users()->error('Failed to send password reset link', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'password_reset_link_failed',
        ]);

        $this->inAppNotificationService->error(
            'Failed to Send Password Reset Link',
            'Unable to send password reset link. Please try again.'
        );

        return false;
    }
}
