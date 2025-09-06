<?php

namespace Database\Seeders;

use App\Enums\GuardEnum;
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
        // User module permissions (for both guards)
        $this->createUserPermissions();

        // Artist module permissions (for both guards)
        $this->createArtistPermissions();
    }

    /**
     * Create user management permissions
     */
    private function createUserPermissions(): void
    {
        // User permissions for api guard (no force delete)
        $userPermissions = PermissionsEnum::getUserPermissions();
        $this->createPermissionGroup($userPermissions, GuardEnum::API->value);

        // Admin permissions for admin guard (with force delete)
        $adminPermissions = PermissionsEnum::getAdminPermissions();
        $this->createPermissionGroup($adminPermissions, GuardEnum::ADMIN->value);
    }

    /**
     * Create artist management permissions
     */
    private function createArtistPermissions(): void
    {
        // Artist permissions are the same for both guards
        $artistPermissions = PermissionsEnum::getArtistPermissions();
        $this->createPermissionGroup($artistPermissions, GuardEnum::API->value);
        $this->createPermissionGroup($artistPermissions, GuardEnum::ADMIN->value);
    }

    /**
     * Create a group of permissions
     */
    private function createPermissionGroup(array $permissions, string $guard = 'web'): void
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission,
                    'guard_name' => $guard,
                ]
            );
        }
    }
}
