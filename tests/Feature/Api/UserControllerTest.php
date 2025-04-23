<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// Helper function to create permissions and roles
function createPermissionsAndRoles() {
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
        Permission::create(['name' => $permission]);
    }

    // Create roles
    $adminRole = Role::create(['name' => 'admin']);
    $managerRole = Role::create(['name' => 'manager']);
    $userRole = Role::create(['name' => 'user']);

    // Assign permissions to roles
    $adminRole->givePermissionTo(Permission::all());
    
    $managerRole->givePermissionTo([
        'users.viewAny',
        'users.view',
        'users.create',
        'users.update',
    ]);
    
    $userRole->givePermissionTo([]);
    
    return [$adminRole, $managerRole, $userRole];
}

beforeEach(function() {
    createPermissionsAndRoles();
    
    // Create and authenticate an admin user
    $user = User::factory()->create();
    $user->assignRole('admin');
    
    Sanctum::actingAs($user);
});

test('index endpoint returns paginated users', function() {
    // Create test users
    User::factory()->count(20)->create();

    // Act - Get the first page with 10 users per page
    $response = $this->getJson(route('users.index', ['page' => 1, 'per_page' => 10]));

    // Assert - Check response structure and data
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total'
            ]
        ])
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.current_page', 1);
});

test('index endpoint validates input parameters', function() {
    // Act - Try with invalid parameters
    $response = $this->getJson(route('users.index', ['page' => 'invalid', 'per_page' => 'invalid']));

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('show endpoint returns the correct user', function() {
    // Create test user
    $user = User::factory()->create();

    // Act - Request specific user
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Check response structure and data
    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
            'deleted_at',
        ])
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', $user->name)
        ->assertJsonPath('email', $user->email);
});

test('show endpoint returns 404 for non-existent user', function() {
    // Act - Request non-existent user
    $response = $this->getJson(route('users.show', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('destroy endpoint deletes the user', function() {
    // Create test user
    $user = User::factory()->create();

    // Act - Delete the user
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Check response and database
    $response->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);

    $this->assertSoftDeleted($user);
});

test('destroy endpoint returns 404 for non-existent user', function() {
    // Act - Try to delete non-existent user
    $response = $this->deleteJson(route('users.destroy', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
    // We only care about the status code, not the exact error message
});

// Unauthorized access tests

test('unauthenticated user cannot access index endpoint', function() {
    // Create a test without authentication
    $this->refreshApplication();
    
    // Act - Try to access index endpoint without authentication
    $response = $this->getJson(route('users.index'));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access show endpoint', function() {
    // Create a test without authentication
    $this->refreshApplication();
    
    // Create a user to try to view
    $user = User::factory()->create();
    
    // Act - Try to access show endpoint without authentication
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access destroy endpoint', function() {
    // Create a test without authentication
    $this->refreshApplication();
    
    // Create a user to try to delete
    $user = User::factory()->create();
    
    // Act - Try to access destroy endpoint without authentication
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('user without permissions cannot access index endpoint', function() {
    // Create and authenticate user with no permissions
    $user = User::factory()->create();
    $user->assignRole('user');
    
    Sanctum::actingAs($user);
    
    // Act - Try to access index endpoint without required permissions
    $response = $this->getJson(route('users.index'));

    // Assert - Check for 403 Forbidden
    $response->assertStatus(403);
});

test('user without permissions cannot view other users', function() {
    // Create a regular user
    $user = User::factory()->create();
    $user->assignRole('user');
    
    // Create another user to try to view
    $anotherUser = User::factory()->create();
    
    Sanctum::actingAs($user);
    
    // Act - Try to view another user without permissions
    $response = $this->getJson(route('users.show', $anotherUser->id));

    // Assert - Check for 403 Forbidden
    $response->assertStatus(403);
});

test('user can view their own profile even without permissions', function() {
    // Create a regular user
    $user = User::factory()->create();
    $user->assignRole('user');
    
    Sanctum::actingAs($user);
    
    // Act - Try to view own profile
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Should succeed (200 OK)
    $response->assertStatus(200)
        ->assertJsonPath('id', $user->id);
});

test('user without permissions cannot delete other users', function() {
    // Create a regular user
    $user = User::factory()->create();
    $user->assignRole('user');
    
    // Create another user to try to delete
    $anotherUser = User::factory()->create();
    
    Sanctum::actingAs($user);
    
    // Act - Try to delete another user without permissions
    $response = $this->deleteJson(route('users.destroy', $anotherUser->id));

    // Assert - Check for 403 Forbidden
    $response->assertStatus(403);
});

test('user cannot delete their own account via API', function() {
    // Create a regular user
    $user = User::factory()->create();
    $user->assignRole('user');
    
    Sanctum::actingAs($user);
    
    // Act - Try to delete own account
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Check for 403 Forbidden (policy prevents self-deletion via API)
    $response->assertStatus(403);
});
