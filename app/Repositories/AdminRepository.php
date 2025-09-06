<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Services\FilterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminRepository
{
    public function __construct(
        private FilterService $filterService
    ) {}

    /**
     * Find an admin by ID
     */
    public function find(int $id): ?Admin
    {
        return Admin::find($id);
    }

    /**
     * Find an admin by ID or throw exception
     */
    public function findOrFail(int $id): Admin
    {
        return Admin::findOrFail($id);
    }

    /**
     * Find an admin with roles and permissions loaded
     */
    public function findWithRoles(int $id): Admin
    {
        return Admin::with(['roles', 'permissions'])->findOrFail($id);
    }

    /**
     * Find an admin with trashed
     */
    public function findWithTrashed(int $id): Admin
    {
        return Admin::withTrashed()->findOrFail($id);
    }

    /**
     * Find an admin by email
     */
    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->first();
    }

    /**
     * Get paginated list of admins with roles and filters
     */
    public function paginate(int $page, int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Admin::with(['roles']);

        $this->filterService->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new admin
     */
    public function create(array $data): Admin
    {
        $admin = Admin::create($data);

        // Load the roles relationship for immediate use
        return $admin->load('roles');
    }

    /**
     * Update an existing admin
     */
    public function update(Admin $admin, array $data): Admin
    {
        $admin->update($data);

        // Load the roles relationship for immediate use
        return $admin->fresh(['roles']);
    }

    /**
     * Delete an admin (soft delete)
     */
    public function delete(Admin $admin): bool
    {
        return $admin->delete();
    }

    /**
     * Restore a soft-deleted admin
     */
    public function restore(Admin $admin): bool
    {
        return $admin->restore();
    }

    /**
     * Restore a soft-deleted admin and return it with relationships loaded
     */
    public function restoreWithRoles(Admin $admin): Admin
    {
        $admin->restore();

        // Load the roles relationship for immediate use
        return $admin->fresh(['roles']);
    }

    /**
     * Force delete an admin
     */
    public function forceDelete(Admin $admin): bool
    {
        return $admin->forceDelete();
    }
}
