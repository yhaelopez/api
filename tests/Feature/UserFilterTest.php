<?php

namespace Tests\Feature;

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

test('superadmin can filter users by search term', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users (should be 1 - just the superadmin)
    $initialCount = User::count();

    // Create test users with different names
    User::factory()->create(['name' => 'John Admin', 'email' => 'admin@example.com']);
    User::factory()->create(['name' => 'Jane User', 'email' => 'jane@example.com']);
    User::factory()->create(['name' => 'Bob Developer', 'email' => 'bob@example.com']);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by search term
    $response = $this->getJson(route('users.index', ['search' => 'john']));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Verify the filtered results contain the expected user
    $responseData = $response->json('data');
    $johnUsers = collect($responseData)->filter(fn ($user) => str_contains(strtolower($user['name']), 'john'));
    expect($johnUsers->count())->toBeGreaterThan(0);
});

test('superadmin can filter users by role name', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different roles
    $adminUser = User::factory()->create(['name' => 'John Admin']);
    $adminUser->assignRole('superadmin');

    $regularUser = User::factory()->create(['name' => 'Jane User']);
    $regularUser->assignRole('member');

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 2);

    // Act - Filter by role name
    $response = $this->getJson(route('users.index', ['role' => 'superadmin']));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Verify we get some results
    expect($filteredCount)->toBeGreaterThan(0);
});

test('superadmin can filter users by role id', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Get role to filter by
    $userRole = \Spatie\Permission\Models\Role::where('name', 'member')->first();

    // Count initial users
    $initialCount = User::count();

    // Create test users with different roles
    $adminUser = User::factory()->create(['name' => 'John Admin']);
    $adminUser->assignRole('superadmin');

    $regularUser1 = User::factory()->create(['name' => 'Jane User']);
    $regularUser1->assignRole('member');

    $regularUser2 = User::factory()->create(['name' => 'Bob Developer']);
    $regularUser2->assignRole('member');

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by role ID
    $response = $this->getJson(route('users.index', ['role_id' => $userRole->id]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return at least 2 users (the ones with 'member' role)
    expect($filteredCount)->toBeGreaterThanOrEqual(2);
});

test('superadmin can filter users by date range', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different creation dates
    User::factory()->create([
        'name' => 'John Admin',
        'created_at' => '2024-01-15 10:00:00',
    ]);

    User::factory()->create([
        'name' => 'Jane User',
        'created_at' => '2024-01-20 11:00:00',
    ]);

    User::factory()->create([
        'name' => 'Bob Developer',
        'created_at' => '2024-02-01 09:00:00',
    ]);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by date range
    $response = $this->getJson(route('users.index', [
        'created_from' => '2024-01-20',
        'created_to' => '2024-01-25',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return at least 1 user in the date range
    expect($filteredCount)->toBeGreaterThan(0);
});

test('superadmin can sort users by name ascending', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different names
    User::factory()->create(['name' => 'John Admin']);
    User::factory()->create(['name' => 'Jane User']);
    User::factory()->create(['name' => 'Bob Developer']);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Sort by name ascending
    $response = $this->getJson(route('users.index', [
        'sort_by' => 'name',
        'sort_direction' => 'asc',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count sorted results (should be same as total)
    $sortedCount = count($response->json('data'));
    expect($sortedCount)->toBe($totalUsers);

    // Verify sorting order
    $responseData = $response->json('data');
    $firstUser = $responseData[0]['name'];
    $lastUser = $responseData[count($responseData) - 1]['name'];

    // First user should come alphabetically before last user
    expect($firstUser)->toBeLessThan($lastUser);
});

test('superadmin can sort users by email ascending', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different emails
    User::factory()->create(['email' => 'admin@example.com']);
    User::factory()->create(['email' => 'bob@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Sort by email ascending
    $response = $this->getJson(route('users.index', [
        'sort_by' => 'email',
        'sort_direction' => 'asc',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count sorted results (should be same as total)
    $sortedCount = count($response->json('data'));
    expect($sortedCount)->toBe($totalUsers);

    // Verify ascending sorting order
    $responseData = $response->json('data');
    $firstUser = $responseData[0]['email'];
    $lastUser = $responseData[count($responseData) - 1]['email'];

    // First user should come alphabetically before last user
    expect($firstUser)->toBeLessThan($lastUser);
});

test('superadmin can sort users by email descending', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different emails
    User::factory()->create(['email' => 'admin@example.com']);
    User::factory()->create(['email' => 'bob@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Sort by email descending
    $response = $this->getJson(route('users.index', [
        'sort_by' => 'email',
        'sort_direction' => 'desc',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count sorted results (should be same as total)
    $sortedCount = count($response->json('data'));
    expect($sortedCount)->toBe($totalUsers);

    // Verify descending sorting order
    $responseData = $response->json('data');
    $firstUser = $responseData[0]['email'];
    $lastUser = $responseData[count($responseData) - 1]['email'];

    // First user should come alphabetically after last user (descending)
    expect($firstUser)->toBeGreaterThan($lastUser);
});

test('search filter rejects short terms', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users
    User::factory()->count(3)->create();

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Search with short term
    $response = $this->getJson(route('users.index', ['search' => 'a']));

    // Assert - Should reject short search terms
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['search']);
});

test('search filter ignores empty terms', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users
    User::factory()->count(3)->create();

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Search with empty term
    $response = $this->getJson(route('users.index', ['search' => '']));

    // Assert - Should return all users (search ignored)
    $response->assertStatus(200);

    // Count results (should be same as total since empty search is ignored)
    $searchCount = count($response->json('data'));
    expect($searchCount)->toBe($totalUsers);
});

test('role id takes priority over role name', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Get role to filter by
    $userRole = \Spatie\Permission\Models\Role::where('name', 'member')->first();

    // Count initial users
    $initialCount = User::count();

    // Create test users with different roles
    $adminUser = User::factory()->create(['name' => 'John Admin']);
    $adminUser->assignRole('superadmin');

    $regularUser1 = User::factory()->create(['name' => 'Jane User']);
    $regularUser1->assignRole('member');

    $regularUser2 = User::factory()->create(['name' => 'Bob Developer']);
    $regularUser2->assignRole('member');

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by both role_id and role (role_id should take priority)
    $response = $this->getJson(route('users.index', [
        'role_id' => $userRole->id,
        'role' => 'superadmin',
    ]));

    // Assert - Should filter by role_id, not role name
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return users with 'member' role (not 'superadmin')
    expect($filteredCount)->toBeGreaterThan(0);
});

test('default sorting is by created_at descending', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different creation dates
    User::factory()->create([
        'name' => 'John Admin',
        'created_at' => '2024-01-15 10:00:00',
    ]);

    User::factory()->create([
        'name' => 'Jane User',
        'created_at' => '2024-01-20 11:00:00',
    ]);

    User::factory()->create([
        'name' => 'Bob Developer',
        'created_at' => '2024-02-01 09:00:00',
    ]);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Get users without specifying sort
    $response = $this->getJson(route('users.index'));

    // Assert - Should use default sorting (created_at desc)
    $response->assertStatus(200);

    // Count results (should be same as total)
    $defaultCount = count($response->json('data'));
    expect($defaultCount)->toBe($totalUsers);

    // Verify default sorting order (most recent first)
    $responseData = $response->json('data');
    $firstUserCreatedAt = $responseData[0]['created_at'];
    $lastUserCreatedAt = $responseData[count($responseData) - 1]['created_at'];

    // First user should be more recent than last user
    expect(strtotime($firstUserCreatedAt))->toBeGreaterThan(strtotime($lastUserCreatedAt));
});

test('can combine multiple filters', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different roles
    $adminUser = User::factory()->create(['name' => 'John Admin']);
    $adminUser->assignRole('superadmin');

    $regularUser1 = User::factory()->create(['name' => 'Jane User']);
    $regularUser1->assignRole('member');

    $regularUser2 = User::factory()->create(['name' => 'Bob Developer']);
    $regularUser2->assignRole('member');

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Combine search, role, and sorting filters
    $response = $this->getJson(route('users.index', [
        'search' => 'user',
        'role' => 'member',
        'sort_by' => 'name',
        'sort_direction' => 'asc',
    ]));

    // Assert - Check combined filtering
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return users that match both search and role criteria
    expect($filteredCount)->toBeGreaterThan(0);
});

test('returns empty results when no matches found', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users
    User::factory()->count(3)->create();

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Search for non-existent term
    $response = $this->getJson(route('users.index', ['search' => 'nonexistent']));

    // Assert - Should return empty results
    $response->assertStatus(200);

    // Count results (should be 0 for non-existent search)
    $searchCount = count($response->json('data'));
    expect($searchCount)->toBe(0);
});

test('unauthorized user cannot access filtered users', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create test users
    User::factory()->count(3)->create();

    // Act - Try to access filtered users
    $response = $this->getJson(route('users.index', ['search' => 'test']));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthenticated user cannot access filtered users', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create test users
    User::factory()->count(3)->create();

    // Act - Try to access filtered users without authentication
    $response = $this->getJson(route('users.index', ['search' => 'test']));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('superadmin can filter users by updated date range', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different update dates
    User::factory()->create([
        'name' => 'John Admin',
        'updated_at' => '2024-01-15 10:00:00',
    ]);

    User::factory()->create([
        'name' => 'Jane User',
        'updated_at' => '2024-01-20 11:00:00',
    ]);

    User::factory()->create([
        'name' => 'Bob Developer',
        'updated_at' => '2024-02-01 09:00:00',
    ]);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by updated date range
    $response = $this->getJson(route('users.index', [
        'updated_from' => '2024-01-20',
        'updated_to' => '2024-01-25',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return at least 1 user in the date range
    expect($filteredCount)->toBeGreaterThan(0);
});

test('superadmin can filter users by deleted date range', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create test users
    $user1 = User::factory()->create(['name' => 'John Admin']);
    $user2 = User::factory()->create(['name' => 'Jane User']);
    $user3 = User::factory()->create(['name' => 'Bob Developer']);

    // Get total users BEFORE deleting (should be 3 + existing users)
    $totalBeforeDeleteResponse = $this->getJson(route('users.index'));
    $totalBeforeDeleteResponse->assertStatus(200);
    $totalBeforeDelete = $totalBeforeDeleteResponse->json('meta.total');

    // Delete users via API
    $this->deleteJson(route('users.destroy', $user1));
    $this->deleteJson(route('users.destroy', $user2));
    $this->deleteJson(route('users.destroy', $user3));

    // Act - Filter deleted users by deletion date range
    $response = $this->getJson(route('users.index', [
        'only_inactive' => '1',
        'deleted_from' => now()->subDays(1)->format('Y-m-d'),
        'deleted_to' => now()->addDays(1)->format('Y-m-d'),
    ]));

    $totalWithDeletedUsers = $response->json('meta.total');

    // Assert - Check response
    $response->assertStatus(200);

    // Should return fewer users than total with deleted (because we're filtering by date range)
    expect($totalWithDeletedUsers)->toBeLessThan($totalBeforeDelete);

    // Verify we got the user deleted on 2024-01-20
    $responseData = $response->json('data');
    $deletedUser = collect($responseData)->firstWhere('name', 'Jane User');
    expect($deletedUser)->not->toBeNull();
});

test('superadmin can filter users by email search', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users with different emails
    User::factory()->create(['name' => 'John Admin', 'email' => 'admin@example.com']);
    User::factory()->create(['name' => 'Jane User', 'email' => 'user@example.com']);
    User::factory()->create(['name' => 'Bob Developer', 'email' => 'developer@example.com']);

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by email search term
    $response = $this->getJson(route('users.index', ['search' => 'admin']));

    // Assert - Check response
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Verify the filtered results contain the expected email
    $responseData = $response->json('data');
    $adminUsers = collect($responseData)->filter(fn ($user) => str_contains(strtolower($user['email']), 'admin'));
    expect($adminUsers->count())->toBeGreaterThan(0);
});

test('superadmin can filter users by numeric role string', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Get role to filter by
    $userRole = \Spatie\Permission\Models\Role::where('name', 'member')->first();

    // Count initial users
    $initialCount = User::count();

    // Create test users with different roles
    $adminUser = User::factory()->create(['name' => 'John Admin']);
    $adminUser->assignRole('superadmin');

    $regularUser1 = User::factory()->create(['name' => 'Jane User']);
    $regularUser1->assignRole('member');

    $regularUser2 = User::factory()->create(['name' => 'Bob Developer']);
    $regularUser2->assignRole('member');

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by role as numeric string (should convert to role_id)
    $response = $this->getJson(route('users.index', ['role' => (string) $userRole->id]));

    // Assert - Should filter by role_id, not role name
    $response->assertStatus(200);

    // Count filtered results
    $filteredCount = count($response->json('data'));

    // Should return fewer users than total when filtering
    expect($filteredCount)->toBeLessThan($totalUsers);

    // Should return users with 'member' role
    expect($filteredCount)->toBeGreaterThan(0);
});

test('superadmin can filter users by non-existent role name', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users
    User::factory()->count(3)->create();

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Filter by non-existent role name
    $response = $this->getJson(route('users.index', ['role' => 'nonexistent_role']));

    // Assert - Should return all users (no filtering applied)
    $response->assertStatus(200);

    // Count results (should be same as total since role doesn't exist)
    $filteredCount = count($response->json('data'));
    expect($filteredCount)->toBe($totalUsers);
});

test('superadmin can sort users by ID', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Count initial users
    $initialCount = User::count();

    // Create test users
    User::factory()->count(3)->create();

    // Count total users after creation
    $totalUsers = User::count();
    expect($totalUsers)->toBe($initialCount + 3);

    // Act - Sort by ID ascending
    $response = $this->getJson(route('users.index', [
        'sort_by' => 'id',
        'sort_direction' => 'asc',
    ]));

    // Assert - Check response
    $response->assertStatus(200);

    // Count sorted results (should be same as total)
    $sortedCount = count($response->json('data'));
    expect($sortedCount)->toBe($totalUsers);

    // Verify sorting order (lowest ID first)
    $responseData = $response->json('data');
    $firstUserId = $responseData[0]['id'];
    $lastUserId = $responseData[count($responseData) - 1]['id'];

    // First user should have lower ID than last user
    expect($firstUserId)->toBeLessThan($lastUserId);
});
