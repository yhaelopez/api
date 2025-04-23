<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestHelper
{
    /**
     * Create permissions and roles for testing
     * 
     * @return array Array containing the created roles
     */
    public static function createPermissionsAndRoles(): array
    {
        // Create permissions
        $permissions = [
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.forceDelete',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'sanctum'
            ]);
        }

        // Create roles
        $superadminRole = Role::create([
            'name' => 'superadmin',
            'guard_name' => 'sanctum'
        ]);
        
        $userRole = Role::create([
            'name' => 'user',
            'guard_name' => 'sanctum'
        ]);

        // Assign permissions to roles
        $superadminRole->givePermissionTo(Permission::all());
        
        $userRole->givePermissionTo([]);
        
        return [$superadminRole, $userRole];
    }

    /**
     * Create and act as a superadmin user for testing
     * 
     * @return User The created superadmin user
     */
    public static function actAsSuperadmin(): User
    {
        $superadmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        
        $role = Role::where('name', 'superadmin')
            ->where('guard_name', 'sanctum')
            ->first();
            
        if ($role) {
            // Insert directly into the pivot table
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $superadmin->id
            ]);
        }
        
        Sanctum::actingAs($superadmin);
        
        return $superadmin;
    }

    /**
     * Create and act as a regular user for testing
     * 
     * @return User The created regular user
     */
    public static function actAsUser(): User
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        
        $role = Role::where('name', 'user')
            ->where('guard_name', 'sanctum')
            ->first();
            
        if ($role) {
            // Insert directly into the pivot table
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $user->id
            ]);
        }
        
        Sanctum::actingAs($user);
        
        return $user;
    }

    /**
     * Create and act as a user with specific permissions for testing
     * 
     * @param array $permissions Array of permission names to assign
     * @return User The created user with the specified permissions
     */
    public static function actAsUserWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create([
            'name' => 'User With Permissions',
            'email' => 'user.with.permissions@example.com',
        ]);
        
        $role = Role::where('name', 'user')
            ->where('guard_name', 'sanctum')
            ->first();
            
        if ($role) {
            // Insert directly into the pivot table
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $user->id
            ]);
        }
        
        // Add specific permissions
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)
                ->where('guard_name', 'sanctum')
                ->first();
                
            if ($permission) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'model_type' => User::class,
                    'model_id' => $user->id
                ]);
            }
        }
        
        Sanctum::actingAs($user);
        
        return $user;
    }
} 