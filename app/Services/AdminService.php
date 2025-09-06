<?php

namespace App\Services;

use App\Cache\AdminCache;
use App\Exceptions\ForceDeleteActiveRecordException;
use App\Models\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AdminService
{
    public function __construct(
        private AdminCache $adminCache,
        private AdminRepository $adminRepository,
        private LoggerService $logger,
        private RoleService $roleService,
        private StorageService $storageService,
        private InAppNotificationService $inAppNotificationService
    ) {}

    /**
     * Get paginated list of admins with caching
     */
    public function getAdminsList(int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->adminCache->rememberList($page, $perPage, $filters, function () use ($page, $perPage, $filters) {
            return $this->adminRepository->paginate($page, $perPage, $filters);
        });
    }

    /**
     * Get a single admin with caching
     */
    public function getAdmin(int $id): Admin
    {
        return $this->adminCache->remember($id, function () use ($id) {
            return $this->adminRepository->findWithRoles($id);
        });
    }

    /**
     * Create a new admin
     */
    public function createAdmin(array $data): Admin
    {
        $data['password'] = Hash::make($data['password']);

        $admin = $this->adminRepository->create($data);

        // Assign role if provided
        if (isset($data['role_id'])) {
            $role = $this->roleService->findRole($data['role_id']);
            if ($role) {
                $this->roleService->assignRole($admin, $role);
            }
        }

        $this->logger->admins()->info('Admin created', [
            'admin_id' => $admin->id,
            'action' => 'admin_created_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Admin Created',
            "Admin '{$admin->name}' has been created successfully."
        );

        return $admin;
    }

    /**
     * Update an existing admin
     */
    public function updateAdmin(Admin $admin, array $data): Admin
    {
        // Handle password hashing if provided (leave blank to keep current password)
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if empty to avoid updating it
            unset($data['password']);
        }

        $updatedAdmin = $this->adminRepository->update($admin, $data);

        // Update role if provided
        if (! empty($data['role_id'])) {
            $role = $this->roleService->findRole($data['role_id']);

            // Remove existing roles and assign new one through service
            $this->roleService->syncRoles($updatedAdmin, [$role]);
        }

        $this->logger->admins()->info('Admin updated', [
            'admin_id' => $admin->id,
            'action' => 'admin_updated_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Admin Updated',
            "Admin '{$updatedAdmin->name}' has been updated successfully."
        );

        return $updatedAdmin;
    }

    /**
     * Delete an admin (soft delete)
     */
    public function deleteAdmin(Admin $admin): bool
    {
        $adminName = $admin->name;

        $this->adminRepository->delete($admin);

        $this->logger->admins()->info('Admin soft deleted', [
            'admin_id' => $admin->id,
            'action' => 'admin_soft_deleted_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Admin Deleted',
            "Admin '{$adminName}' has been moved to trash."
        );

        return true;
    }

    /**
     * Add profile photo to existing admin
     */
    public function addProfilePhoto(Admin $admin, UploadedFile $profilePhoto): void
    {
        // Clear existing profile photo (single file collection)
        $admin->clearMediaCollection('profile_photos');

        // Add new profile photo
        $admin->addMedia(
            file: $profilePhoto
        )
            ->usingFileName(
                fileName: $this->storageService->generateProfilePhotoFilename($profilePhoto)
            )
            ->toMediaCollection(
                collectionName: 'profile_photos',
                diskName: $this->storageService->getProfilePhotoDisk()
            );

        $this->logger->admins()->info('Profile photo added to admin', [
            'admin_id' => $admin->id,
            'filename' => $profilePhoto->getClientOriginalName(),
            'action' => 'profile_photo_added',
        ]);
    }

    /**
     * Remove profile photo from admin
     */
    public function removeProfilePhoto(Admin $admin): bool
    {
        if (! $admin->hasMedia('profile_photos')) {
            // Send warning notification to current user
            $this->inAppNotificationService->warning(
                'No Profile Photo',
                'This admin does not have a profile photo to remove.'
            );

            return false;
        }

        // Clear the profile photos collection (removes files and database records)
        $admin->clearMediaCollection('profile_photos');

        $this->logger->admins()->info('Profile photo removed from admin', [
            'admin_id' => $admin->id,
            'action' => 'profile_photo_removed',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Profile Photo Removed',
            "Profile photo for '{$admin->name}' has been removed successfully."
        );

        return true;
    }

    /**
     * Restore a soft-deleted admin
     */
    public function restore(Admin $admin): Admin
    {
        $restoredAdmin = $this->adminRepository->restoreWithRoles($admin);

        $this->logger->admins()->info('Admin restored', [
            'admin_id' => $admin->id,
            'action' => 'admin_restored_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Admin Restored',
            "Admin '{$restoredAdmin->name}' has been restored successfully."
        );

        return $restoredAdmin;
    }

    /**
     * Force delete an admin permanently
     *
     * @throws ForceDeleteActiveRecordException When attempting to force delete an active admin
     */
    public function forceDeleteAdmin(Admin $admin): bool
    {
        // Check if admin is soft-deleted before force deleting
        if (! $admin->trashed()) {
            $this->logger->admins()->alert('Attempted to force delete active admin', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'action' => 'force_delete_active_admin_attempt',
            ]);

            throw new ForceDeleteActiveRecordException(
                modelClass: Admin::class,
                modelId: $admin->id
            );
        }

        $adminName = $admin->name;
        $this->adminRepository->forceDelete($admin);

        $this->logger->admins()->info('Admin permanently deleted', [
            'admin_id' => $admin->id,
            'action' => 'admin_permanently_deleted_success',
        ]);

        // Send warning notification to current user
        $this->inAppNotificationService->warning(
            'Admin Permanently Deleted',
            "Admin '{$adminName}' has been permanently deleted and cannot be recovered."
        );

        return true;
    }

    /**
     * Send password reset link to the admin.
     */
    public function sendPasswordResetLink(Admin $admin): bool
    {
        $status = Password::broker('admins')->sendResetLink(['email' => $admin->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->logger->admins()->info('Password reset link sent', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'action' => 'password_reset_link_sent',
            ]);

            $this->inAppNotificationService->success(
                'Password Reset Link Sent',
                "Password reset link has been sent to {$admin->email}."
            );

            return true;
        }

        $this->logger->admins()->error('Failed to send password reset link', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'action' => 'password_reset_link_failed',
        ]);

        $this->inAppNotificationService->error(
            'Failed to Send Password Reset Link',
            'Unable to send password reset link. Please try again.'
        );

        return false;
    }
}
