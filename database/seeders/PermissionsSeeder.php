<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        $this->resetPermissionsCache();

        // Create all permissions
        $this->createPermissions();
    }

    /**
     * Reset the permissions cache
     */
    private function resetPermissionsCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
        // User module permissions
        $this->createUserPermissions();

        // Add other modules' permissions here as needed
    }

    /**
     * Create user management permissions
     */
    private function createUserPermissions(): void
    {
        $userPermissions = PermissionsEnum::getUserPermissions();

        $this->createPermissionGroup($userPermissions);
    }

    /**
     * Create a group of permissions
     */
    private function createPermissionGroup(array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
