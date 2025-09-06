<?php

namespace Tests\Feature\Api;

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

// V1 API TESTS

test('superadmin can view all artists', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create test artists
    Artist::factory()->count(20)->create();

    // Act - Get the first page with 10 artists per page
    $response = $this->getJson(route('artists.index', ['page' => 1, 'per_page' => 10]));

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

test('superadmin can view any artist profile', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create a random artist
    $randomArtist = Artist::factory()->create();

    // Act - Request the artist
    $response = $this->getJson(route('artists.show', $randomArtist->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('id', $randomArtist->id);
});

test('superadmin can delete any artist', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist to delete
    $artistToDelete = Artist::factory()->create();

    // Act - Delete the artist
    $response = $this->deleteJson(route('artists.destroy', $artistToDelete->id));

    // Assert - Check response and database
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist deleted']);

    $this->assertSoftDeleted($artistToDelete);
});

// USER WITH SPECIFIC PERMISSIONS TESTS

test('authorized user can view all artists', function () {
    // Act as user with view permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.viewAny');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create test artists
    Artist::factory()->count(5)->create();

    // Act - Get artists list
    $response = $this->getJson(route('artists.index', ['per_page' => 10]));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
});

test('authorized user can view other artist profiles', function () {
    // Act as user with view permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.view');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    // Act - View other artist
    $response = $this->getJson(route('artists.show', $otherArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $otherArtist->id);
});

test('user without view permission can still view own artists', function () {
    // Act as unauthorized user with no permissions
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create artist owned by this user
    $ownArtist = Artist::factory()->forOwner($user)->create();

    // Act - View own artist
    $response = $this->getJson(route('artists.show', $ownArtist->id));

    // Assert - Should succeed because users can view their own artists
    $response->assertStatus(200)
        ->assertJsonPath('id', $ownArtist->id);
});

test('authorized user can delete other artists', function () {
    // Act as user with delete permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.delete');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    // Act - Delete other artist
    $response = $this->deleteJson(route('artists.destroy', $otherArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist deleted']);

    $this->assertSoftDeleted($otherArtist);
});

test('user can delete their own artists', function () {
    // Act as regular user
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create artist owned by this user
    $ownArtist = Artist::factory()->forOwner($user)->create();

    // Act - Delete own artist
    $response = $this->deleteJson(route('artists.destroy', $ownArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist deleted']);

    $this->assertSoftDeleted($ownArtist);
});

// UNAUTHORIZED USER ACCESS TESTS

test('unauthorized user cannot view all artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create test artists
    Artist::factory()->count(5)->create();

    // Act - Try to get artists list
    $response = $this->getJson(route('artists.index'));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user can view their own artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create artist owned by this user
    $ownArtist = Artist::factory()->forOwner($user)->create();

    // Act - Request own artist
    $response = $this->getJson(route('artists.show', $ownArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('id', $ownArtist->id)
        ->assertJsonPath('name', $ownArtist->name);
});

test('unauthorized user cannot view other artist profiles', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    // Act - Try to view other artist
    $response = $this->getJson(route('artists.show', $otherArtist->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthorized user can delete their own artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create artist owned by this user
    $ownArtist = Artist::factory()->forOwner($user)->create();

    // Act - Try to delete own artist
    $response = $this->deleteJson(route('artists.destroy', $ownArtist->id));

    // Assert - Should succeed (owners can delete their own artists)
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist deleted']);

    $this->assertSoftDeleted($ownArtist);
});

test('unauthorized user cannot delete other artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    // Act - Try to delete other artist
    $response = $this->deleteJson(route('artists.destroy', $otherArtist->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

// ADDITIONAL HELPER TESTS

test('index endpoint validates input parameters', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Try with invalid parameters
    $response = $this->getJson(route('artists.index', ['page' => 'invalid', 'per_page' => 'invalid']));

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('show endpoint returns 404 for non-existent artist', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Request non-existent artist
    $response = $this->getJson(route('artists.show', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('destroy endpoint returns 404 for non-existent artist', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Try to delete non-existent artist
    $response = $this->deleteJson(route('artists.destroy', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

// UNAUTHORIZED ACCESS TESTS

test('unauthenticated user cannot access index endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Act - Try to access index endpoint without authentication
    $response = $this->getJson(route('artists.index'));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access show endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create an artist to try to view
    $artist = Artist::factory()->create();

    // Act - Try to access show endpoint without authentication
    $response = $this->getJson(route('artists.show', $artist->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access destroy endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create an artist to try to delete
    $artist = Artist::factory()->create();

    // Act - Try to access destroy endpoint without authentication
    $response = $this->deleteJson(route('artists.destroy', $artist->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('rate limiting is enforced for artist endpoints', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Make 61 requests (exceeding the 60 per minute limit)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->getJson(route('artists.index'));

        // The 61st request should be rate limited
        if ($i === 60) {
            $response->assertStatus(429); // Too Many Requests

            return;
        }

        // First 60 requests should succeed
        $response->assertStatus(200);
    }
});

test('cache is invalidated when new artist is created', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create some initial test artists to ensure we have a baseline
    Artist::factory()->count(3)->create();

    // First request - should hit database
    $response1 = $this->getJson(route('artists.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');

    // Second request - should hit cache
    $response2 = $this->getJson(route('artists.index'));
    $response2->assertStatus(200);

    // Create a new artist (this should invalidate cache)
    Artist::factory()->create();

    // Third request - should hit database again due to cache invalidation
    $response3 = $this->getJson(route('artists.index'));
    $response3->assertStatus(200);
    $newCount = $response3->json('meta.total');

    // Count should have increased by 1
    $this->assertEquals($initialCount + 1, $newCount);
});

test('cached response returns old data when database changes manually', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Ensure we have a clean starting point
    Artist::query()->forceDelete();

    // Create some test artists to ensure we have a baseline
    Artist::factory()->count(3)->create();

    // First request - should hit database and cache the result
    $response1 = $this->getJson(route('artists.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');
    $this->assertEquals(3, $initialCount, 'Should have exactly 3 artists initially');

    // Second request - should hit cache and return same count
    $response2 = $this->getJson(route('artists.index'));
    $response2->assertStatus(200);
    $this->assertEquals($initialCount, $response2->json('meta.total'), 'Second request should return cached result');

    // Create user outside of the Artist context to avoid any potential observer interactions
    $existingUser = User::first() ?? User::factory()->create();

    // Manually insert an artist directly in the database (bypassing the observer)
    DB::table('artists')->insert([
        'owner_id' => $existingUser->id,
        'name' => 'Manual Artist',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify the artist was actually added to database by checking the actual count
    $actualDbCount = Artist::count();
    $this->assertEquals(4, $actualDbCount, 'Manual insert should add artist to database for total of 4');

    // Third request - should still hit cache (not invalidated by manual DB insert)
    // This should return the cached count, not the new database count
    $response3 = $this->getJson(route('artists.index'));
    $response3->assertStatus(200);
    $cachedCount = $response3->json('meta.total');

    // Count should still be the same as initial count (cached response)
    $this->assertEquals(3, $cachedCount, 'Cache should not be invalidated by manual DB insert');
});

test('cache is invalidated when artist is deleted via API', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create some initial test artists and one to delete
    Artist::factory()->count(3)->create();
    $artistToDelete = Artist::factory()->create();

    // First request - should hit database
    $response1 = $this->getJson(route('artists.index'));
    $response1->assertStatus(200);
    $initialCount = $response1->json('meta.total');

    // Second request - should hit cache
    $response2 = $this->getJson(route('artists.index'));
    $response2->assertStatus(200);

    // Delete the artist via API (this should invalidate cache)
    $response = $this->deleteJson(route('artists.destroy', $artistToDelete->id));
    $response->assertStatus(200);

    // Third request - should hit database again due to cache invalidation
    $response3 = $this->getJson(route('artists.index'));
    $response3->assertStatus(200);
    $newCount = $response3->json('meta.total');

    // Count should have decreased by 1
    $this->assertEquals($initialCount - 1, $newCount);
});

test('update method validates spotify_id uniqueness when changing spotify_id', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create two artists
    $artist1 = Artist::factory()->create(['spotify_id' => 'artist1_id']);
    // Artist with a taken spotify_id
    Artist::factory()->create(['spotify_id' => 'artist2_id']);

    // Act - Try to update artist1 with artist2's spotify_id
    $response = $this->putJson(route('artists.update', $artist1->id), [
        'spotify_id' => 'artist2_id',
    ]);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['spotify_id']);
});

// STORE METHOD TESTS

test('superadmin can create a new artist', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    $artistData = [
        'name' => 'New Artist',
        'spotify_id' => 'new_artist_123',
    ];

    // Act - Create new artist
    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'spotify_id',
            'created_at',
            'updated_at',
        ])
        ->assertJsonPath('name', $artistData['name'])
        ->assertJsonPath('spotify_id', $artistData['spotify_id']);

    // Check database
    $this->assertDatabaseHas('artists', [
        'name' => $artistData['name'],
        'spotify_id' => $artistData['spotify_id'],
        'owner_id' => $superadmin->id, // Should be set to current user
    ]);
});

test('authorized user can create a new artist', function () {
    // Act as user with create permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.create');
    $this->actingAs($user, GuardEnum::WEB->value);

    $artistData = [
        'name' => 'Another Artist',
        'spotify_id' => 'another_artist_456',
    ];

    // Act - Create new artist
    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Should succeed
    $response->assertStatus(201)
        ->assertJsonPath('name', $artistData['name'])
        ->assertJsonPath('spotify_id', $artistData['spotify_id']);

    // Check that owner is set to current user
    $this->assertDatabaseHas('artists', [
        'name' => $artistData['name'],
        'owner_id' => $user->id,
    ]);
});

test('unauthorized user cannot create a new artist', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    $artistData = [
        'name' => 'Unauthorized Artist',
        'spotify_id' => 'unauthorized_123',
    ];

    // Act - Try to create new artist
    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('store method validates required fields', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Try with missing required fields
    $response = $this->postJson(route('artists.store'), []);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('store method validates spotify_id uniqueness', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create existing artist
    Artist::factory()->create(['spotify_id' => 'existing_spotify_id']);

    // Act - Try with duplicate spotify_id
    $response = $this->postJson(route('artists.store'), [
        'name' => 'Test Artist',
        'spotify_id' => 'existing_spotify_id',
    ]);

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['spotify_id']);
});

// UPDATE METHOD TESTS

test('superadmin can update any artist', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist to update
    $artistToUpdate = Artist::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'spotify_id' => 'updated_spotify_id',
    ];

    // Act - Update the artist
    $response = $this->putJson(route('artists.update', $artistToUpdate->id), $updateData);

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name'])
        ->assertJsonPath('spotify_id', $updateData['spotify_id']);

    // Check database
    $this->assertDatabaseHas('artists', [
        'id' => $artistToUpdate->id,
        'name' => $updateData['name'],
        'spotify_id' => $updateData['spotify_id'],
    ]);
});

test('authorized user can update other artists', function () {
    // Act as user with update permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.update');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist to update
    $otherArtist = Artist::factory()->create();

    $updateData = [
        'name' => 'Updated by Authorized User',
    ];

    // Act - Update the other artist
    $response = $this->putJson(route('artists.update', $otherArtist->id), $updateData);

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);
});

test('user can update their own artists', function () {
    // Act as regular user
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create artist owned by this user
    $ownArtist = Artist::factory()->forOwner($user)->create();

    $updateData = [
        'name' => 'My Updated Artist',
    ];

    // Act - Update own artist
    $response = $this->putJson(route('artists.update', $ownArtist->id), $updateData);

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);
});

test('unauthorized user cannot update other artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    $updateData = [
        'name' => 'Unauthorized Update',
    ];

    // Act - Try to update other artist
    $response = $this->putJson(route('artists.update', $otherArtist->id), $updateData);

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthenticated user cannot access store endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    $artistData = [
        'name' => 'Test Artist',
        'spotify_id' => 'test_spotify_id',
    ];

    // Act - Try to access store endpoint without authentication
    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access update endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create an artist to try to update
    $artist = Artist::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
    ];

    // Act - Try to access update endpoint without authentication
    $response = $this->putJson(route('artists.update', $artist->id), $updateData);

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

// RESTORE METHOD TESTS

test('superadmin can restore a soft-deleted artist', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create and soft-delete an artist
    $deletedArtist = Artist::factory()->create();
    $deletedArtist->delete();

    // Verify artist is soft-deleted
    $this->assertSoftDeleted($deletedArtist);

    // Act - Restore the artist
    $response = $this->postJson(route('artists.restore', $deletedArtist->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Artist restored',
        ])
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'spotify_id',
                'created_at',
                'updated_at',
            ],
        ]);

    // Check database - artist should no longer be soft-deleted
    $this->assertDatabaseHas('artists', [
        'id' => $deletedArtist->id,
        'deleted_at' => null,
    ]);
});

test('authorized user can restore other artists', function () {
    // Act as user with restore permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.restore');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create and soft-delete another artist
    $deletedArtist = Artist::factory()->create();
    $deletedArtist->delete();

    // Act - Restore the artist
    $response = $this->postJson(route('artists.restore', $deletedArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist restored']);
});

test('user can restore their own artists', function () {
    // Act as regular user
    $user = TestHelper::createTestUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create and soft-delete own artist
    $ownArtist = Artist::factory()->forOwner($user)->create();
    $ownArtist->delete();

    // Act - Restore own artist
    $response = $this->postJson(route('artists.restore', $ownArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist restored']);
});

test('unauthorized user cannot restore other artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create and soft-delete another artist
    $deletedArtist = Artist::factory()->create();
    $deletedArtist->delete();

    // Act - Try to restore the artist
    $response = $this->postJson(route('artists.restore', $deletedArtist->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('restore endpoint returns 404 for non-existent artist', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Try to restore non-existent artist
    $response = $this->postJson(route('artists.restore', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

// FORCE DELETE METHOD TESTS

test('superadmin can permanently delete an artist', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist to permanently delete
    $artistToDelete = Artist::factory()->create();
    $artistToDelete->delete();

    // Act - Permanently delete the artist
    $response = $this->deleteJson(route('artists.force-delete', $artistToDelete->id));

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist permanently deleted']);

    // Check database - artist should be completely removed
    $this->assertDatabaseMissing('artists', [
        'id' => $artistToDelete->id,
    ]);
});

test('authorized user can permanently delete other artists', function () {
    // Act as user with force delete permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('artists.forceDelete');
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist to permanently delete
    $otherArtist = Artist::factory()->create();
    $otherArtist->delete();

    // Act - Permanently delete the other artist
    $response = $this->deleteJson(route('artists.force-delete', $otherArtist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist permanently deleted']);
});

test('unauthorized user cannot permanently delete artists', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::WEB->value);

    // Create another artist
    $otherArtist = Artist::factory()->create();

    // Act - Try to permanently delete the other artist
    $response = $this->deleteJson(route('artists.force-delete', $otherArtist->id));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('force delete endpoint returns 404 for non-existent artist', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Try to permanently delete non-existent artist
    $response = $this->deleteJson(route('artists.force-delete', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('force delete endpoint returns 422 for active (non-deleted) artist', function () {
    // Act as superadmin for this test
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an active artist (not soft-deleted)
    $activeArtist = Artist::factory()->create();

    // Act - Try to permanently delete the active artist
    $response = $this->deleteJson(route('artists.force-delete', $activeArtist->id));

    // Assert - Should return 422 because onlyTrashed() only allows soft-deleted artists
    $response->assertStatus(422);
});

test('force delete works only with soft-deleted artists', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create and soft-delete an artist
    $deletedArtist = Artist::factory()->create();
    $deletedArtist->delete();

    // Verify artist is soft-deleted
    $this->assertSoftDeleted($deletedArtist);

    // Act - Permanently delete the soft-deleted artist
    $response = $this->deleteJson(route('artists.force-delete', $deletedArtist->id));

    // Assert - Should succeed because artist is soft-deleted
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist permanently deleted']);

    // Check database - artist should be completely removed
    $this->assertDatabaseMissing('artists', [
        'id' => $deletedArtist->id,
    ]);
});

test('force delete workflow: delete then force delete', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist
    $artist = Artist::factory()->create();

    // First, soft-delete the artist
    $response = $this->deleteJson(route('artists.destroy', $artist->id));
    $response->assertStatus(200);

    // Verify artist is soft-deleted
    $this->assertSoftDeleted($artist);

    // Now try to force delete the soft-deleted artist
    $response = $this->deleteJson(route('artists.force-delete', $artist->id));

    // Assert - Should succeed
    $response->assertStatus(200)
        ->assertJson(['message' => 'Artist permanently deleted']);

    // Check database - artist should be completely removed
    $this->assertDatabaseMissing('artists', [
        'id' => $artist->id,
    ]);
});

test('force delete with onlyTrashed prevents deletion of active artists', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create multiple artists
    $activeArtist1 = Artist::factory()->create();
    $activeArtist2 = Artist::factory()->create();

    // Create and soft-delete one artist
    $deletedArtist = Artist::factory()->create();
    $deletedArtist->delete();

    // Try to force delete active artists - should fail
    $response1 = $this->deleteJson(route('artists.force-delete', $activeArtist1->id));
    $response2 = $this->deleteJson(route('artists.force-delete', $activeArtist2->id));

    // Assert - Both should return 422
    $response1->assertStatus(422);
    $response2->assertStatus(422);

    // Verify active artists still exist
    $this->assertDatabaseHas('artists', ['id' => $activeArtist1->id]);
    $this->assertDatabaseHas('artists', ['id' => $activeArtist2->id]);

    // Now force delete the soft-deleted artist - should succeed
    $response3 = $this->deleteJson(route('artists.force-delete', $deletedArtist->id));
    $response3->assertStatus(200);

    // Verify soft-deleted artist is completely removed
    $this->assertDatabaseMissing('artists', ['id' => $deletedArtist->id]);
});

test('force delete triggers ForceDeleteActiveRecordException for active artists', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an active artist (not soft-deleted)
    $activeArtist = Artist::factory()->create();

    // Act - Try to force delete the active artist
    $response = $this->deleteJson(route('artists.force-delete', $activeArtist->id));

    // Assert - Should return 422 with exception details
    $response->assertStatus(422)
        ->assertJsonStructure([
            'error',
            'message',
        ])
        ->assertJson([
            'error' => 422,
            'message' => 'Cannot force delete active Artist with ID '.$activeArtist->id.'. The record must be soft-deleted first.',
        ]);

    // Verify the active artist still exists in database
    $this->assertDatabaseHas('artists', [
        'id' => $activeArtist->id,
        'deleted_at' => null,
    ]);
});

test('unauthenticated user cannot access restore endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create an artist to try to restore
    $artist = Artist::factory()->create();

    // Act - Try to access restore endpoint without authentication
    $response = $this->postJson(route('artists.restore', $artist->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

test('unauthenticated user cannot access force delete endpoint', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Create an artist to try to permanently delete
    $artist = Artist::factory()->create();

    // Act - Try to access force delete endpoint without authentication
    $response = $this->deleteJson(route('artists.force-delete', $artist->id));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});

// PROFILE PHOTO UPLOAD TESTS

test('superadmin can create artist with profile photo using temp folder', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    $artistData = [
        'name' => 'Artist With Photo',
        'spotify_id' => 'artist_with_photo_123',
        'temp_folder' => 'test_folder_123',
    ];

    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonPath('name', $artistData['name'])
        ->assertJsonPath('spotify_id', $artistData['spotify_id']);

    // Note: In the system, profile photos are handled via temp_folder
    // The actual file processing happens in the TemporaryFileService
    $createdArtist = Artist::where('name', $artistData['name'])->first();
    $this->assertNotNull($createdArtist);
});

test('superadmin can update artist with profile photo using temp folder', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // First, create an artist without profile photo
    $artistData = [
        'name' => 'Artist Without Photo',
        'spotify_id' => 'artist_without_photo_456',
    ];

    $createResponse = $this->postJson(route('artists.store'), $artistData);
    $createResponse->assertStatus(201);

    $createdArtist = Artist::where('name', $artistData['name'])->first();
    $this->assertNotNull($createdArtist);

    // Now update the artist with a profile photo using temp folder
    $updateData = [
        'name' => 'Updated Name With Photo',
        'temp_folder' => 'test_folder_456',
    ];

    $updateResponse = $this->putJson(route('artists.update', $createdArtist->id), $updateData);

    // Assert - Check response
    $updateResponse->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);

    // Note: In the system, profile photos are handled via temp_folder
    // The actual file processing happens in the TemporaryFileService
    $updatedArtist = $createdArtist->fresh();
    $this->assertNotNull($updatedArtist);
});

test('artist resource includes profile_photo field when available', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist
    $artist = Artist::factory()->create();

    // Act - Get the artist
    $response = $this->getJson(route('artists.show', $artist->id));

    // Assert - Check response structure
    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'spotify_id',
            'profile_photo', // This field should be present (even if null)
            'created_at',
            'updated_at',
        ]);

    // The profile_photo field should be present but null for artists without photos
    $this->assertArrayHasKey('profile_photo', $response->json());
    $this->assertNull($response->json('profile_photo'));
});

test('store method accepts temp_folder field', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    $artistData = [
        'name' => 'Artist With Temp Folder',
        'spotify_id' => 'artist_with_temp_folder_789',
        'temp_folder' => 'test_folder_789',
    ];

    // Act - Create new artist with temp_folder
    $response = $this->postJson(route('artists.store'), $artistData);

    // Assert - Check response
    $response->assertStatus(201)
        ->assertJsonPath('name', $artistData['name'])
        ->assertJsonPath('spotify_id', $artistData['spotify_id']);

    // Check database
    $this->assertDatabaseHas('artists', [
        'name' => $artistData['name'],
        'spotify_id' => $artistData['spotify_id'],
    ]);
});

test('update method accepts temp_folder field', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Create an artist to update
    $artistToUpdate = Artist::factory()->create();

    $updateData = [
        'name' => 'Updated Name With Temp Folder',
        'temp_folder' => 'update_folder_123',
    ];

    // Act - Update the artist with temp_folder
    $response = $this->putJson(route('artists.update', $artistToUpdate->id), $updateData);

    // Assert - Check response
    $response->assertStatus(200)
        ->assertJsonPath('name', $updateData['name']);

    // Check database
    $this->assertDatabaseHas('artists', [
        'id' => $artistToUpdate->id,
        'name' => $updateData['name'],
    ]);
});
