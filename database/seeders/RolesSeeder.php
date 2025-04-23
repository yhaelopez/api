<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create all roles
        $this->createRoles();

        // Then, create admin users that need these roles
        $this->createAdminUsers();
    }

    /**
     * Create all required roles
     */
    private function createRoles(): void
    {
        // Create the superadmin role
        $this->createSuperadminRole();
        
        // Create the basic user role
        $this->createUserRole();
        
        // Add additional roles as needed
        // $this->createEditorRole();
        // $this->createModeratorRole();
    }

    /**
     * Create superadmin role with all permissions
     */
    private function createSuperadminRole(): void
    {
        $role = Role::create([
            'name' => 'superadmin',
            'guard_name' => 'sanctum'
        ]);
        
        // Assign all permissions to superadmin
        $role->givePermissionTo([
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.forceDelete',
        ]);
    }

    /**
     * Create basic user role
     */
    private function createUserRole(): void
    {
        $role = Role::create([
            'name' => 'user',
            'guard_name' => 'sanctum'
        ]);
        
        // Regular users can only view their own profile
        $role->givePermissionTo([
            'users.view',  // Can view their own profile
        ]);
    }

    /**
     * Create admin users
     */
    private function createAdminUsers(): void
    {
        // Create a superadmin user for testing
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);

        // Get the superadmin role ID
        $role = Role::where('name', 'superadmin')
              ->where('guard_name', 'sanctum')
              ->first();
              
        if ($role) {
            // Insert directly into the pivot table
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $superAdmin->id
            ]);
        } else {
            throw new \Exception("Superadmin role with sanctum guard not found");
        }
    }
}
