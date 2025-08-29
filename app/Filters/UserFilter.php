<?php

namespace App\Filters;

use App\Services\RoleService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles all filtering logic for User models
 */
class UserFilter extends BaseFilter
{
    /**
     * Constructor
     *
     * @param  array  $filters  The filter data from the request
     * @param  RoleService  $roleService  Service for role operations
     */
    public function __construct(
        array $filters,
        private RoleService $roleService
    ) {
        parent::__construct($filters);
    }

    /**
     * Apply all filters to the query
     *
     * @param  Builder  $query  The query builder
     */
    public function apply(Builder $query): void
    {
        $this->applySearchFilter($query);
        $this->applyRoleFilter($query);
        $this->applyDateFilters($query);
        $this->applySortBy($query);
        $this->applyWithInactiveFilter($query);
        $this->applyOnlyActiveFilter($query);
        $this->applyDeletedUsersFilter($query);
    }

    /**
     * Apply search filter by name or email
     *
     * @param  Builder  $query  The query builder
     */
    private function applySearchFilter(Builder $query): void
    {
        $search = $this->getString('search');

        if (! $search || strlen($search) < 2) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Apply role filter by role name or ID
     *
     * @param  Builder  $query  The query builder
     */
    private function applyRoleFilter(Builder $query): void
    {
        $role = $this->get('role');
        $roleId = $this->getInt('role_id');

        if ($roleId) {
            $this->applyRoleIdFilter($query, $roleId);

            return;
        }

        if ($role) {
            $this->applyRoleNameFilter($query, $role);
        }
    }

    /**
     * Apply role filter using role ID
     *
     * @param  Builder  $query  The query builder
     * @param  int  $roleId  The role ID to filter by
     */
    private function applyRoleIdFilter(Builder $query, int $roleId): void
    {
        $query->whereHas('roles', function ($q) use ($roleId) {
            $q->where('roles.id', $roleId);
        });
    }

    /**
     * Apply role filter using role name (converts to ID first)
     *
     * @param  Builder  $query  The query builder
     * @param  mixed  $role  The role name or numeric ID
     */
    private function applyRoleNameFilter(Builder $query, mixed $role): void
    {
        if (is_numeric($role)) {
            $this->applyRoleIdFilter($query, (int) $role);

            return;
        }

        $roleModel = $this->roleService->findRoleByName($role);

        if ($roleModel) {
            $this->applyRoleIdFilter($query, $roleModel->id);
        }
    }

    /**
     * Get user-specific sortable fields
     *
     * @return array List of user-specific sortable field names
     */
    protected function getModelSpecificSortableFields(): array
    {
        return [
            'name',
            'email',
        ];
    }
}
