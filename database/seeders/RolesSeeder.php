<?php

namespace Database\Seeders;

use App\Enums\GuardEnum;
use App\Enums\PermissionsEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create all roles
        $this->createRoles();
    }

    /**
     * Create all required roles
     */
    private function createRoles(): void
    {
        // Create roles for both guards
        $this->createRolesForGuard(GuardEnum::API->value);
        $this->createRolesForGuard(GuardEnum::ADMIN->value);
    }

    /**
     * Create roles for a specific guard
     */
    private function createRolesForGuard(string $guard): void
    {
        if ($guard === GuardEnum::ADMIN->value) {
            // Only superadmin for admin guard
            $this->createSuperAdminRole($guard);
        } else {
            // Only member for api guard
            $this->createMemberRole($guard);
        }
    }

    /**
     * Create superadmin role with all permissions
     */
    private function createSuperAdminRole(string $guard): void
    {
        $role = Role::firstOrCreate([
            'name' => RoleEnum::SUPERADMIN->value,
            'guard_name' => $guard,
        ]);

        // Assign admin permissions (with force delete) to superadmin
        $role->givePermissionTo(PermissionsEnum::getAdminPermissions());
    }

    /**
     * Create basic member role
     */
    private function createMemberRole(string $guard): void
    {
        $role = Role::firstOrCreate([
            'name' => RoleEnum::MEMBER->value,
            'guard_name' => $guard,
        ]);

        // Regular members can only view their own profile (no force delete)
        $role->givePermissionTo(PermissionsEnum::getUserPermissions());
        $role->givePermissionTo(PermissionsEnum::getArtistPermissions());
    }
}
