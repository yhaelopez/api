<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('it can remove user profile photo', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $user->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Verify the photo exists
    expect($user->hasMedia('profile_photos'))->toBeTrue();

    // Remove the profile photo
    $response = $this->deleteJson("/api/v1/users/{$user->id}/profile-photo");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);

    // Verify the photo is removed
    $user->refresh();
    expect($user->hasMedia('profile_photos'))->toBeFalse();
});

test('it returns 404 when trying to remove non-existent profile photo', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // Try to remove profile photo when none exists
    $response = $this->deleteJson("/api/v1/users/{$user->id}/profile-photo");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'No profile photo found',
        ]);
});

test('it requires authentication to remove profile photo', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Add a profile photo first
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $user->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Try to remove without authentication
    $response = $this->deleteJson("/api/v1/users/{$user->id}/profile-photo");

    $response->assertStatus(401);

    // Verify the photo still exists
    expect($user->hasMedia('profile_photos'))->toBeTrue();
});

test('user cannot remove another users profile photo', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();

    // Add a profile photo to user1
    $file = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $user1->addMedia($file)
        ->toMediaCollection('profile_photos', 'public');

    // Try to remove user1's photo as user2
    $this->actingAs($user2);
    $response = $this->deleteJson("/api/v1/users/{$user1->id}/profile-photo");

    $response->assertStatus(403);

    // Verify the photo still exists
    expect($user1->hasMedia('profile_photos'))->toBeTrue();
});

