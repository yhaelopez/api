<?php

namespace App\Services;

use App\Events\User\UserUpdated;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    /**
     * Get paginated list of roles
     */
    public function getRolesList(int $page = 1, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->roleRepository->paginate($page, $perPage);
    }

    /**
     * Get paginated list of roles filtered by guard
     */
    public function getRolesListByGuard(string $guard, int $page = 1, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->roleRepository->paginateByGuard($guard, $page, $perPage);
    }

    /**
     * Get all available roles (for internal use)
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
    }

    /**
     * Get all available roles filtered by guard
     */
    public function getAllRolesByGuard(string $guard): Collection
    {
        return $this->roleRepository->getAllByGuard($guard);
    }

    /**
     * Find a role by ID
     */
    public function findRole(int $id)
    {
        return $this->roleRepository->find($id);
    }

    /**
     * Find a role by name
     */
    public function findRoleByName(string $name)
    {
        return $this->roleRepository->findByName($name);
    }

    /**
     * Sync roles for a model
     */
    public function syncRoles(Model $model, array $roles): void
    {
        $model->syncRoles($roles);
        event(new UserUpdated($model));
    }

    /**
     * Assign a role to a model
     */
    public function assignRole(Model $model, $role): void
    {
        $model->assignRole($role);
        event(new UserUpdated($model));
    }
}
