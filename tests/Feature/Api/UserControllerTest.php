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

// Create a superadmin user for testing
function actAsSuperadmin() {
    // createPermissionsAndRoles();
    
    $superadmin = User::factory()->create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole('superadmin');
    
    Sanctum::actingAs($superadmin);
    
    return $superadmin;
}

// Create a unauthorized user for testing
function actAsUser() {
    // createPermissionsAndRoles();
    
    $user = User::factory()->create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
    ]);
    $user->assignRole('user');
    
    Sanctum::actingAs($user);
    
    return $user;
}

// Create a user with specific permissions for testing
function actAsUserWithPermissions(array $permissions = []) {
    // createPermissionsAndRoles();
    
    $user = User::factory()->create([
        'name' => 'User With Permissions',
        'email' => 'user.with.permissions@example.com',
    ]);
    $user->assignRole('user');
    
    foreach ($permissions as $permission) {
        $user->givePermissionTo($permission);
    }
    
    Sanctum::actingAs($user);
    
    return $user;
}

beforeEach(function() {
    // By default, run as superadmin
    createPermissionsAndRoles();
    actAsSuperadmin();
});

// SUPERADMIN ACCESS TESTS

test('superadmin can view all users', function() {
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

test('superadmin can view any user profile', function() {
    // Create a random user
    $randomUser = User::factory()->create();
    
    // Act - Request the user
    $response = $this->getJson(route('users.show', $randomUser->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('id', $randomUser->id);
});

test('superadmin can delete any user', function() {
    // Create a user to delete
    $userToDelete = User::factory()->create();
    
    // Act - Delete the user
    $response = $this->deleteJson(route('users.destroy', $userToDelete->id));
    
    // Assert - Check response and database
    $response->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);
    
    $this->assertSoftDeleted($userToDelete);
});

// USER WITH SPECIFIC PERMISSIONS TESTS

test('authorized user can view all users', function() {
    // Act as user with viewAny permission
    actAsUserWithPermissions(['users.viewAny']);
    
    // Create test users
    User::factory()->count(5)->create();
    
    // Act - Get users list
    $response = $this->getJson(route('users.index', ['per_page' => 10]));
    
    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
});

test('authorized user can view other user profiles', function() {
    // Act as user with view permission
    actAsUserWithPermissions(['users.view']);
    
    // Create another user
    $otherUser = User::factory()->create();
    
    // Act - View other user
    $response = $this->getJson(route('users.show', $otherUser->id));
    
    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $otherUser->id);
});

test('user without view permission can still view own profile', function() {
    // Act as unauthorized user with no permissions
    $user = actAsUser();
    
    // Act - View own profile
    $response = $this->getJson(route('users.show', $user->id));
    
    // Assert - Should succeed because users can view their own profile
    $response->assertStatus(200)
        ->assertJsonPath('id', $user->id);
});

test('authorized user can delete other users', function() {
    // Act as user with delete permission
    actAsUserWithPermissions(['users.delete']);
    
    // Create another user
    $otherUser = User::factory()->create();
    
    // Act - Delete other user
    $response = $this->deleteJson(route('users.destroy', $otherUser->id));
    
    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);
    
    $this->assertSoftDeleted($otherUser);
});

test('authorized user cannot delete themselves', function() {
    // Act as user with delete permission
    $user = actAsUserWithPermissions(['users.delete']);
    
    // Act - Try to delete self
    $response = $this->deleteJson(route('users.destroy', $user->id));
    
    // Assert - Should be forbidden per policy
    $response->assertStatus(403);
});

// unauthorized user ACCESS TESTS

test('unauthorized user cannot view all users', function() {
    // Act as regular user
    actAsUser();
    
    // Create test users
    User::factory()->count(5)->create();
    
    // Act - Try to get users list
    $response = $this->getJson(route('users.index'));
    
    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user can view their own user', function() {
    // Act as regular user
    $user = actAsUser();
    
    // Act - Request own user
    $response = $this->getJson(route('users.show', $user->id));
    
    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

test('unauthorized user cannot view other user profiles', function() {
    // Act as regular user
    actAsUser();
    
    // Create another user
    $otherUser = User::factory()->create();
    
    // Act - Try to view other user
    $response = $this->getJson(route('users.show', $otherUser->id));
    
    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user cannot delete any user including themselves', function() {
    // Act as regular user
    $user = actAsUser();
    
    // Create another user
    $otherUser = User::factory()->create();
    
    // Act - Try to delete other user
    $response = $this->deleteJson(route('users.destroy', $otherUser->id));
    
    // Assert - Should be forbidden
    $response->assertStatus(403);
    
    // Act - Try to delete self
    $response = $this->deleteJson(route('users.destroy', $user->id));
    
    // Assert - Should be forbidden (per policy)
    $response->assertStatus(403);
});

// ADDITIONAL HELPER TESTS

test('index endpoint validates input parameters', function() {
    // Act - Try with invalid parameters
    $response = $this->getJson(route('users.index', ['page' => 'invalid', 'per_page' => 'invalid']));

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('show endpoint returns 404 for non-existent user', function() {
    // Act - Request non-existent user
    $response = $this->getJson(route('users.show', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('destroy endpoint returns 404 for non-existent user', function() {
    // Act - Try to delete non-existent user
    $response = $this->deleteJson(route('users.destroy', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

// UNAUTHORIZED ACCESS TESTS

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
