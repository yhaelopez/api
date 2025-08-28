<?php

namespace Database\Seeders;

use App\Enums\GuardEnum;
use App\Enums\PermissionsEnum;
use App\Enums\RoleEnum;
use App\Models\User;
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
        // Create the superadmin role
        $this->createTestSuperAdminRole();

        // Create the basic user role
        $this->createUserRole();

        // Add additional roles as needed
    }

    /**
     * Create superadmin role with all permissions
     */
    private function createTestSuperAdminRole(): void
    {
        $role = Role::create([
            'name' => RoleEnum::SUPERADMIN->value,
            'guard_name' => GuardEnum::WEB->value,
        ]);

        // Assign all permissions to superadmin
        $role->givePermissionTo(PermissionsEnum::getAllPermissions());
    }

    /**
     * Create basic user role
     */
    private function createUserRole(): void
    {
        $role = Role::create([
            'name' => RoleEnum::USER->value,
            'guard_name' => GuardEnum::WEB->value,
        ]);

        // Regular users can only view their own profile
        $role->givePermissionTo(PermissionsEnum::getUserPermissions());
    }
}
