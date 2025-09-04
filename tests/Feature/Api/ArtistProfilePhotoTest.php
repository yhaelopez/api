<?php

use App\Helpers\TestHelper;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

test('it can remove artist profile photo', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();
    $this->actingAs($user);

    // Create an artist owned by the user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($user)->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();

    // Remove the profile photo
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify the photo is removed
    $artist->refresh();
    expect($artist->hasMedia('profile_photos'))->toBeFalse();
});

test('it returns 404 when trying to remove non-existent profile photo', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();
    $this->actingAs($user);

    // Create an artist owned by the user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($user)->create();

    // Try to remove profile photo when none exists
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'No profile photo found',
        ]);
});

test('it requires authentication to remove profile photo', function () {
    /** @var Artist $artist */
    $artist = Artist::factory()->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Try to remove without authentication
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(401);

    // Verify the photo still exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();
});

test('user cannot remove another users artist profile photo', function () {
    /** @var User $user1 */
    $user1 = TestHelper::createTestUser();
    /** @var User $user2 */
    $user2 = TestHelper::createTestUnauthorizedUser();

    // Create an artist owned by user1
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($user1)->create();

    // Add a profile photo to user1's artist
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Try to remove user1's artist photo as user2
    $this->actingAs($user2);
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(403);

    // Verify the photo still exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();
});

test('user can remove their own artist profile photo', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();

    // Create an artist owned by the user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($user)->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();

    // Remove the profile photo as the owner
    $this->actingAs($user);
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify the photo is removed
    $artist->refresh();
    expect($artist->hasMedia('profile_photos'))->toBeFalse();
});

test('superadmin can remove any artist profile photo', function () {
    /** @var User $regularUser */
    $regularUser = TestHelper::createTestUser();

    // Create an artist owned by regular user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($regularUser)->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();

    // Create and act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin);

    // Remove the profile photo as superadmin
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify the photo is removed
    $artist->refresh();
    expect($artist->hasMedia('profile_photos'))->toBeFalse();
});

test('authorized user can remove other artist profile photos', function () {
    /** @var User $artistOwner */
    $artistOwner = TestHelper::createTestUser();
    /** @var User $authorizedUser */
    $authorizedUser = TestHelper::createTestUser();

    // Give the user permission to update artists
    $authorizedUser->givePermissionTo('artists.update');

    // Create an artist owned by another user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($artistOwner)->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();

    // Remove the profile photo as authorized user
    $this->actingAs($authorizedUser);
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify the photo is removed
    $artist->refresh();
    expect($artist->hasMedia('profile_photos'))->toBeFalse();
});

test('it handles multiple artists with profile photos correctly', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();
    $this->actingAs($user);

    // Create two artists owned by the user
    /** @var Artist $artist1 */
    $artist1 = Artist::factory()->forOwner($user)->create();
    /** @var Artist $artist2 */
    $artist2 = Artist::factory()->forOwner($user)->create();

    // Add profile photos to both
    $file1 = UploadedFile::fake()->image('profile1.jpg', 200, 200);
    $file2 = UploadedFile::fake()->image('profile2.jpg', 200, 200);

    $artist1->addMedia($file1)
        ->toMediaCollection('profile_photos', 'public');
    $artist2->addMedia($file2)
        ->toMediaCollection('profile_photos', 'public');

    // Verify both photos exist
    expect($artist1->hasMedia('profile_photos'))->toBeTrue();
    expect($artist2->hasMedia('profile_photos'))->toBeTrue();

    // Remove only artist1's profile photo
    $response = $this->deleteJson("/api/v1/artists/{$artist1->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify artist1's photo is removed but artist2's still exists
    $artist1->refresh();
    $artist2->refresh();
    expect($artist1->hasMedia('profile_photos'))->toBeFalse();
    expect($artist2->hasMedia('profile_photos'))->toBeTrue();
});

test('it returns proper error for non-existent artist', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();
    $this->actingAs($user);

    // Try to remove profile photo for non-existent artist
    $response = $this->deleteJson('/api/v1/artists/999999/profile-photo');

    $response->assertStatus(404);
});

test('it handles file cleanup correctly when removing profile photo', function () {
    /** @var User $user */
    $user = TestHelper::createTestUser();
    $this->actingAs($user);

    // Create an artist owned by the user
    /** @var Artist $artist */
    $artist = Artist::factory()->forOwner($user)->create();

    // Add a profile photo
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $artist->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($artist->hasMedia('profile_photos'))->toBeTrue();

    // Remove the profile photo
    $response = $this->deleteJson("/api/v1/artists/{$artist->id}/profile-photo");

    $response->assertStatus(200);

    // Verify the database record was removed
    $artist->refresh();
    expect($artist->hasMedia('profile_photos'))->toBeFalse();
});
