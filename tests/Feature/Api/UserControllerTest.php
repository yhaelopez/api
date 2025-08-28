<?php

namespace Tests\Feature\Api;

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

// SUPERADMIN ACCESS TESTS

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
