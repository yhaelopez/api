<?php

namespace Tests\Feature\Api;

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

// V1 API TESTS

test('superadmin can view all users', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

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
                'total',
            ],
        ])
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.current_page', 1);
});

test('superadmin can view any user profile', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a random user
    $randomUser = User::factory()->create();

    // Act - Request the user
    $response = $this->getJson(route('users.show', $randomUser->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('id', $randomUser->id);
});

test('superadmin can delete any user', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

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

test('authorized user can view all users', function () {
    // Act as user with view permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.viewAny');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create test users
    User::factory()->count(5)->create();

    // Act - Get users list
    $response = $this->getJson(route('users.index', ['per_page' => 10]));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
});

test('authorized user can view other user profiles', function () {
    // Act as user with view permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.view');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user
    $otherUser = User::factory()->create();

    // Act - View other user
    $response = $this->getJson(route('users.show', $otherUser->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $otherUser->id);
});

test('user without view permission can still view own profile', function () {
    // Act as unauthorized user with no permissions
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Act - View own profile
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Should succeed because users can view their own profile
    $response->assertStatus(200)
        ->assertJsonPath('id', $user->id);
});

test('authorized user can delete other users', function () {
    // Act as user with delete permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.delete');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user
    $otherUser = User::factory()->create();

    // Act - Delete other user
    $response = $this->deleteJson(route('users.destroy', $otherUser->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);

    $this->assertSoftDeleted($otherUser);
});

test('authorized user cannot delete themselves', function () {
    // Act as user with delete permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.delete');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Act - Try to delete self
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Should be forbidden per policy
    $response->assertStatus(403);
});

// unauthorized user ACCESS TESTS

test('unauthorized user cannot view all users', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create test users
    User::factory()->count(5)->create();

    // Act - Try to get users list
    $response = $this->getJson(route('users.index'));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user can view their own user', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Act - Request own user
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

test('unauthorized user cannot view other user profiles', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user
    $otherUser = User::factory()->create();

    // Act - Try to view other user
    $response = $this->getJson(route('users.show', $otherUser->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user cannot delete any user including themselves', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

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

test('index endpoint validates input parameters', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Try with invalid parameters
    $response = $this->getJson(route('users.index', ['page' => 'invalid', 'per_page' => 'invalid']));

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('show endpoint returns 404 for non-existent user', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Request non-existent user
    $response = $this->getJson(route('users.show', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('destroy endpoint returns 404 for non-existent user', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Try to delete non-existent user
    $response = $this->deleteJson(route('users.destroy', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

// UNAUTHORIZED ACCESS TESTS

test('unauthenticated user cannot access index endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Act - Try to access index endpoint without authentication
    $response = $this->getJson(route('users.index'));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access show endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create a user to try to view
    $user = User::factory()->create();

    // Act - Try to access show endpoint without authentication
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access destroy endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create a user to try to delete
    $user = User::factory()->create();

    // Act - Try to access destroy endpoint without authentication
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('rate limiting is enforced for user endpoints', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Make 61 requests (exceeding the 60 per minute limit)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->getJson(route('users.index'));

        // The 61st request should be rate limited
        if ($i === 60) {
            $response->assertStatus(429); // Too Many Requests

            return;
        }

        // First 60 requests should succeed
        $response->assertStatus(200);
    }
});

test('cache is invalidated when new user is created', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create some test users
    User::factory()->count(5)->create();

    // First request - should hit database
    $response1 = $this->getJson(route('users.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');

    // Second request - should hit cache
    $response2 = $this->getJson(route('users.index'));
    $response2->assertStatus(200);

    // Create a new user (this should invalidate cache)
    User::factory()->create();

    // Third request - should hit database again due to cache invalidation
    $response3 = $this->getJson(route('users.index'));
    $response3->assertStatus(200);
    $newCount = $response3->json('meta.total');

    // Count should have increased by 1
    $this->assertEquals($initialCount + 1, $newCount);
});

test('cached response returns old data when database changes manually', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // First request - should hit database
    $response1 = $this->getJson(route('users.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');

    // Second request - should hit cache
    $response2 = $this->getJson(route('users.index'));
    $response2->assertStatus(200);

    // Manually insert a user directly in the database (bypassing the observer)
    DB::table('users')->insert([
        'name' => 'Manual User',
        'email' => 'manual@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Third request - should still hit cache (not invalidated by manual DB insert)
    $response3 = $this->getJson(route('users.index'));
    $response3->assertStatus(200);
    $cachedCount = $response3->json('meta.total');

    // Count should still be the same (cached response)
    $this->assertEquals($initialCount, $cachedCount);

    // Verify the user was actually added to database by checking the actual count
    $actualDbCount = User::count();
    $this->assertGreaterThan($initialCount, $actualDbCount, 'New user should be added to database');
});

test('cache is invalidated when user is deleted via API', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user to delete
    $userToDelete = User::factory()->create();

    // First request - should hit database
    $response1 = $this->getJson(route('users.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');

    // Second request - should hit cache
    $response2 = $this->getJson(route('users.index'));
    $response2->assertStatus(200);

    // Delete the user via API (this should invalidate cache)
    $response = $this->deleteJson(route('users.destroy', $userToDelete->id));
    $response->assertStatus(200);

    // Third request - should hit database again due to cache invalidation
    $response3 = $this->getJson(route('users.index'));
    $response3->assertStatus(200);
    $newCount = $response3->json('meta.total');

    // Count should have decreased by 1
    $this->assertEquals($initialCount - 1, $newCount);
});

test('update method validates email uniqueness when changing email', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create two users
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    // User with a taken email
    User::factory()->create(['email' => 'user2@example.com']);

    // Act - Try to update user1 with user2's email
    $response = $this->putJson(route('users.update', $user1->id), [
        'email' => 'user2@example.com',
    ]);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

// STORE METHOD TESTS

test('superadmin can create a new user', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    $userData = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
    ];

    // Act - Create new user
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ])
        ->assertJsonPath('name', $userData['name'])
        ->assertJsonPath('email', $userData['email']);

    // Check database
    $this->assertDatabaseHas('users', [
        'name' => $userData['name'],
        'email' => $userData['email'],
    ]);
});

test('authorized user can create a new user', function () {
    // Act as user with create permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.create');
    $this->actingAs($user, GuardEnum::WEB->value);

    $userData = [
        'name' => 'Another User',
        'email' => 'anotheruser@example.com',
        'password' => 'password123',
    ];

    // Act - Create new user
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Should succeed
    $response->assertStatus(201)
        ->assertJsonPath('name', $userData['name'])
        ->assertJsonPath('email', $userData['email']);
});

test('unauthorized user cannot create a new user', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    $userData = [
        'name' => 'Unauthorized User',
        'email' => 'unauthorized@example.com',
        'password' => 'password123',
    ];

    // Act - Try to create new user
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('store method validates required fields', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Try with missing required fields
    $response = $this->postJson(route('users.store'), []);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('store method validates email format and uniqueness', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create existing user
    User::factory()->create(['email' => 'existing@example.com']);

    // Act - Try with invalid email and duplicate email
    $response = $this->postJson(route('users.store'), [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
    ]);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('store method can create user with role', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Get a role to assign
    $role = \Spatie\Permission\Models\Role::where('name', 'user')->first();

    $userData = [
        'name' => 'User With Role',
        'email' => 'userwithrole@example.com',
        'password' => 'password123',
        'role_id' => $role->id,
    ];

    // Act - Create new user with role
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonPath('name', $userData['name'])
        ->assertJsonPath('email', $userData['email']);

    // Check database
    $this->assertDatabaseHas('users', [
        'name' => $userData['name'],
        'email' => $userData['email'],
    ]);

    // Check that role was assigned
    $createdUser = User::where('email', $userData['email'])->first();
    $this->assertTrue($createdUser->hasRole($role));
});

test('store method validates role_id exists', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    $userData = [
        'name' => 'User With Invalid Role',
        'email' => 'userwithinvalidrole@example.com',
        'password' => 'password123',
        'role_id' => 999999, // Non-existent role ID
    ];

    // Act - Try to create user with invalid role
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role_id']);
});

// UPDATE METHOD TESTS

test('superadmin can update any user', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user to update
    $userToUpdate = TestHelper::createTestUser();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    // Act - Update the user
    $response = $this->putJson(route('users.update', $userToUpdate->id), $updateData);

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name'])
        ->assertJsonPath('email', $updateData['email']);

    // Check database
    $this->assertDatabaseHas('users', [
        'id' => $userToUpdate->id,
        'name' => $updateData['name'],
        'email' => $updateData['email'],
    ]);
});

test('authorized user can update other users', function () {
    // Act as user with update permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.update');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user to update
    $otherUser = User::factory()->create();

    $updateData = [
        'name' => 'Updated by Authorized User',
    ];

    // Act - Update the other user
    $response = $this->putJson(route('users.update', $otherUser->id), $updateData);

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);
});

test('user can update their own profile', function () {
    // Act as regular user
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    $updateData = [
        'name' => 'My Updated Name',
        'email' => 'myupdated@'.$this->faker->domainName(),
    ];

    // Act - Update own profile
    $response = $this->putJson(route('users.update', $user->id), $updateData);

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name'])
        ->assertJsonPath('email', $updateData['email']);
});

test('unauthorized user cannot update other users', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user
    $otherUser = TestHelper::createTestUser();

    $updateData = [
        'name' => 'Unauthorized Update',
    ];

    // Act - Try to update other user
    $response = $this->putJson(route('users.update', $otherUser->id), $updateData);

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthenticated user cannot access store endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    $userData = [
        'name' => 'Test User',
        'email' => 'test@'.$this->faker->domainName(),
        'password' => 'password123',
    ];

    // Act - Try to access store endpoint without authentication
    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access update endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create a user to try to update
    $user = TestHelper::createTestUser();

    $updateData = [
        'name' => 'Updated Name',
    ];

    // Act - Try to access update endpoint without authentication
    $response = $this->putJson(route('users.update', $user->id), $updateData);

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('update method can update user role', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user to update
    $userToUpdate = TestHelper::createTestUser();

    // Get a different role to assign
    $newRole = \Spatie\Permission\Models\Role::where('name', 'superadmin')->first();

    $updateData = [
        'name' => 'Updated Name',
        'role_id' => $newRole->id,
    ];

    // Act - Update the user with new role
    $response = $this->putJson(route('users.update', $userToUpdate->id), $updateData);

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);

    // Check that role was updated
    $updatedUser = $userToUpdate->fresh();
    $this->assertTrue($updatedUser->hasRole($newRole));
});

test('update method validates role_id exists', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user to update
    $userToUpdate = TestHelper::createTestUser();

    $updateData = [
        'name' => 'Updated Name',
        'role_id' => 999999, // Non-existent role ID
    ];

    // Act - Try to update user with invalid role
    $response = $this->putJson(route('users.update', $userToUpdate->id), $updateData);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role_id']);
});

// RESTORE METHOD TESTS

test('superadmin can restore a soft-deleted user', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create and soft-delete a user
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    // Verify user is soft-deleted
    $this->assertSoftDeleted($deletedUser);

    // Act - Restore the user
    $response = $this->postJson(route('users.restore', $deletedUser->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User restored successfully',
        ])
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

    // Check database - user should no longer be soft-deleted
    $this->assertDatabaseHas('users', [
        'id' => $deletedUser->id,
        'deleted_at' => null,
    ]);
});

test('authorized user can restore other users', function () {
    // Act as user with restore permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.restore');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create and soft-delete another user
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    // Act - Restore the user
    $response = $this->postJson(route('users.restore', $deletedUser->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'User restored successfully']);
});

test('unauthorized user cannot restore users', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create and soft-delete another user
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    // Act - Try to restore the user
    $response = $this->postJson(route('users.restore', $deletedUser->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('restore endpoint returns 404 for non-existent user', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Try to restore non-existent user
    $response = $this->postJson(route('users.restore', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

// FORCE DELETE METHOD TESTS

test('superadmin can permanently delete a user', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user to permanently delete
    $userToDelete = User::factory()->create();
    $userToDelete->delete();

    // Act - Permanently delete the user
    $response = $this->deleteJson(route('users.force-delete', $userToDelete->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJson(['message' => 'User permanently deleted successfully']);

    // Check database - user should be completely removed
    $this->assertDatabaseMissing('users', [
        'id' => $userToDelete->id,
    ]);
});

test('authorized user can permanently delete other users', function () {
    // Act as user with force delete permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.forceDelete');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user to permanently delete
    $otherUser = User::factory()->create();
    $otherUser->delete();

    // Act - Permanently delete the other user
    $response = $this->deleteJson(route('users.force-delete', $otherUser->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'User permanently deleted successfully']);
});

test('unauthorized user cannot permanently delete users', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another user
    $otherUser = User::factory()->create();

    // Act - Try to permanently delete the other user
    $response = $this->deleteJson(route('users.force-delete', $otherUser->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('force delete endpoint returns 404 for non-existent user', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Act - Try to permanently delete non-existent user
    $response = $this->deleteJson(route('users.force-delete', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('force delete endpoint returns 422 for active (non-deleted) user', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create an active user (not soft-deleted)
    $activeUser = User::factory()->create();

    // Act - Try to permanently delete the active user
    $response = $this->deleteJson(route('users.force-delete', $activeUser->id));

    // Assert - Should return 422 because onlyTrashed() only allows soft-deleted users
    $response->assertStatus(422);
});

test('force delete works only with soft-deleted users', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create and soft-delete a user
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    // Verify user is soft-deleted
    $this->assertSoftDeleted($deletedUser);

    // Act - Permanently delete the soft-deleted user
    $response = $this->deleteJson(route('users.force-delete', $deletedUser->id));

    // Assert - Should succeed because user is soft-deleted
    $response->assertStatus(200)
        ->assertJson(['message' => 'User permanently deleted successfully']);

    // Check database - user should be completely removed
    $this->assertDatabaseMissing('users', [
        'id' => $deletedUser->id,
    ]);
});

test('force delete workflow: delete then force delete', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a user
    $user = User::factory()->create();

    // First, soft-delete the user
    $response = $this->deleteJson(route('users.destroy', $user->id));
    $response->assertStatus(200);

    // Verify user is soft-deleted
    $this->assertSoftDeleted($user);

    // Now try to force delete the soft-deleted user
    $response = $this->deleteJson(route('users.force-delete', $user->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'User permanently deleted successfully']);

    // Check database - user should be completely removed
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('force delete with onlyTrashed prevents deletion of active users', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create multiple users
    $activeUser1 = User::factory()->create();
    $activeUser2 = User::factory()->create();

    // Create and soft-delete one user
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    // Try to force delete active users - should fail
    $response1 = $this->deleteJson(route('users.force-delete', $activeUser1->id));
    $response2 = $this->deleteJson(route('users.force-delete', $activeUser2->id));

    // Assert - Both should return 422
    $response1->assertStatus(422);
    $response2->assertStatus(422);

    // Verify active users still exist
    $this->assertDatabaseHas('users', ['id' => $activeUser1->id]);
    $this->assertDatabaseHas('users', ['id' => $activeUser2->id]);

    // Now force delete the soft-deleted user - should succeed
    $response3 = $this->deleteJson(route('users.force-delete', $deletedUser->id));
    $response3->assertStatus(200);

    // Verify soft-deleted user is completely removed
    $this->assertDatabaseMissing('users', ['id' => $deletedUser->id]);
});

test('force delete triggers ForceDeleteActiveRecordException for active users', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create an active user (not soft-deleted)
    $activeUser = User::factory()->create();

    // Act - Try to force delete the active user
    $response = $this->deleteJson(route('users.force-delete', $activeUser->id));

    // Assert - Should return 422 with exception details
    $response->assertStatus(422)
        ->assertJsonStructure([
            'error',
            'message',
        ])
        ->assertJson([
            'error' => 422,
            'message' => 'Cannot force delete active User with ID '.$activeUser->id.'. The record must be soft-deleted first.',
        ]);

    // Verify the active user still exists in database
    $this->assertDatabaseHas('users', [
        'id' => $activeUser->id,
        'deleted_at' => null,
    ]);
});

test('unauthenticated user cannot access restore endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create a user to try to restore
    $user = User::factory()->create();

    // Act - Try to access restore endpoint without authentication
    $response = $this->postJson(route('users.restore', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access force delete endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create a user to try to permanently delete
    $user = User::factory()->create();

    // Act - Try to access force delete endpoint without authentication
    $response = $this->deleteJson(route('users.force-delete', $user->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

// PROFILE PHOTO UPLOAD TEST

test('superadmin can create user with profile photo file', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::WEB->value);

    // Create a fake image file
    $profilePhoto = File::image('profile.jpg', 100, 100);

    $userData = [
        'name' => 'User With Photo',
        'email' => 'userwithphoto@example.com',
        'password' => 'password123',
        'profile_photo' => $profilePhoto,
    ];

    $response = $this->postJson(route('users.store'), $userData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonPath('name', $userData['name'])
        ->assertJsonPath('email', $userData['email']);

    // Check that profile photo was uploaded
    $createdUser = User::where('email', $userData['email'])->first();
    $this->assertTrue($createdUser->hasMedia('profile_photos'));
});
