<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    /**
     * Get paginated list of roles
     */
    public function paginate(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return Role::orderBy('name')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all roles
     */
    public function getAll(): Collection
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Find a role by ID
     */
    public function find(int $id): ?Role
    {
        return Role::find($id);
    }

    /**
     * Find a role by name
     */
    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }
}
