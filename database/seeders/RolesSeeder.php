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
        $this->createRolesForGuard(GuardEnum::WEB->value);
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
            // Only user for web guard
            $this->createUserRole($guard);
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
     * Create basic user role
     */
    private function createUserRole(string $guard): void
    {
        $role = Role::firstOrCreate([
            'name' => RoleEnum::USER->value,
            'guard_name' => $guard,
        ]);

        // Regular users can only view their own profile (no force delete)
        $role->givePermissionTo(PermissionsEnum::getUserPermissions());
        $role->givePermissionTo(PermissionsEnum::getArtistPermissions());
    }
}
