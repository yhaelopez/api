<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
        // User module permissions
        $this->createUserPermissions();

        // Add other modules' permissions here as needed
        // $this->createArticlePermissions();
        // $this->createCommentPermissions();
        // etc.
    }

    /**
     * Create user management permissions
     */
    private function createUserPermissions(): void
    {
        $userPermissions = [
            'users.viewAny',   // Can view the user list
            'users.view',      // Can view user details
            'users.create',    // Can create new users
            'users.update',    // Can update existing users
            'users.delete',    // Can soft delete users
            'users.restore',   // Can restore soft-deleted users
            'users.forceDelete', // Can permanently delete users
        ];

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
                'guard_name' => 'sanctum'
            ]);
        }
    }
}
