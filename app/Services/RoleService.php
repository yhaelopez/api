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
     * Get all available roles
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
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
