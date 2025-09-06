<?php

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Artist API', function () {
    describe('index', function () {
        test('superadmin can view all artists', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artists
            Artist::factory()->count(20)->create();

            // Act - Get the first page with 10 artists per page
            $response = $this->getJson(route('admin.v1.artists.index', ['page' => 1, 'per_page' => 10]));

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
                ->assertJsonPath('meta.current_page', 1)
                ->assertJsonPath('meta.total', 20);
        });
    });

    describe('store', function () {
        test('superadmin can create artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user for owner
            $user = User::factory()->regularUser()->create();

            // Act
            $response = $this->postJson(route('admin.v1.artists.store'), [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
                'owner_id' => $user->id,
            ]);

            // Assert
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'name',
                    'spotify_id',
                    'owner',
                    'profile_photo',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'restored_at',
                ])
                ->assertJsonPath('name', 'Test Artist')
                ->assertJsonPath('spotify_id', 'test-spotify-id')
                ->assertJsonPath('owner.id', $user->id);

            // Verify artist was created in database
            $this->assertDatabaseHas('artists', [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
                'owner_id' => $user->id,
            ]);
        });

        test('superadmin can create artist without owner', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->postJson(route('admin.v1.artists.store'), [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
            ]);

            // Assert
            $response->assertStatus(201)
                ->assertJsonPath('name', 'Test Artist')
                ->assertJsonPath('owner', null);

            // Verify artist was created in database
            $this->assertDatabaseHas('artists', [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
                'owner_id' => null,
            ]);
        });

        test('artist creation requires name', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->postJson(route('admin.v1.artists.store'), [
                'spotify_id' => 'test-spotify-id',
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        test('artist creation validates spotify_id uniqueness', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create existing artist
            Artist::factory()->create(['spotify_id' => 'existing-spotify-id']);

            // Act
            $response = $this->postJson(route('admin.v1.artists.store'), [
                'name' => 'Test Artist',
                'spotify_id' => 'existing-spotify-id',
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['spotify_id']);
        });

        test('artist creation validates owner_id exists', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->postJson(route('admin.v1.artists.store'), [
                'name' => 'Test Artist',
                'owner_id' => 999, // Non-existent user ID
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['owner_id']);
        });
    });

    describe('show', function () {
        test('superadmin can view any artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artist
            $artist = Artist::factory()->create();

            // Act
            $response = $this->getJson(route('admin.v1.artists.show', $artist));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'name',
                    'spotify_id',
                    'profile_photo',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'restored_at',
                ])
                ->assertJsonPath('id', $artist->id)
                ->assertJsonPath('name', $artist->name);
        });
    });

    describe('update', function () {
        test('superadmin can update any artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artist
            $artist = Artist::factory()->create(['name' => 'Original Name']);

            // Act
            $response = $this->putJson(route('admin.v1.artists.update', $artist), [
                'name' => 'Updated Name',
                'spotify_id' => 'updated-spotify-id',
            ]);

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('name', 'Updated Name')
                ->assertJsonPath('spotify_id', 'updated-spotify-id');

            // Verify artist was updated in database
            $this->assertDatabaseHas('artists', [
                'id' => $artist->id,
                'name' => 'Updated Name',
                'spotify_id' => 'updated-spotify-id',
            ]);
        });

        test('artist update validates spotify_id uniqueness', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create existing artists
            $artist1 = Artist::factory()->create(['spotify_id' => 'existing-spotify-id']);
            $artist2 = Artist::factory()->create(['spotify_id' => 'other-spotify-id']);

            // Act
            $response = $this->putJson(route('admin.v1.artists.update', $artist2), [
                'name' => 'Updated Name',
                'spotify_id' => 'existing-spotify-id',
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['spotify_id']);
        });
    });

    describe('destroy', function () {
        test('superadmin can delete any artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artist
            $artist = Artist::factory()->create();

            // Act
            $response = $this->deleteJson(route('admin.v1.artists.destroy', $artist));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'Artist deleted');

            // Verify artist was soft deleted
            $this->assertSoftDeleted('artists', ['id' => $artist->id]);
        });
    });

    describe('restore', function () {
        test('superadmin can restore deleted artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create and delete test artist
            $artist = Artist::factory()->create();
            $artist->delete();

            // Act
            $response = $this->postJson(route('admin.v1.artists.restore', $artist));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'Artist restored')
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                    ],
                ]);

            // Verify artist was restored
            $this->assertDatabaseHas('artists', [
                'id' => $artist->id,
                'deleted_at' => null,
            ]);
        });

    });

    describe('forceDelete', function () {
        test('superadmin can force delete artist', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create and delete test artist
            $artist = Artist::factory()->create();
            $artist->delete();

            // Act
            $response = $this->deleteJson(route('admin.v1.artists.force-delete', $artist));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'Artist permanently deleted');

            // Verify artist was permanently deleted
            $this->assertDatabaseMissing('artists', ['id' => $artist->id]);
        });

    });

    describe('removeProfilePhoto', function () {
        test('superadmin can remove artist profile photo', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artist with profile photo
            $artist = Artist::factory()->create();

            // Create a fake image file for testing
            $fakeFile = UploadedFile::fake()->image('test-photo.jpg', 100, 100);
            $fakeFile->storeAs('temp', 'test-photo.jpg', 'public');

            $artist->addMediaFromDisk('temp/test-photo.jpg', 'public')
                ->toMediaCollection('profile_photos');

            // Act
            $response = $this->deleteJson(route('admin.v1.artists.profile-photo.delete', $artist));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'Profile photo removed successfully');

            // Verify profile photo was removed
            expect($artist->fresh()->getFirstMedia('profile_photos'))->toBeNull();

            // Clean up the test file
            Storage::disk('public')->delete('temp/test-photo.jpg');
        });

        test('returns 404 when no profile photo exists', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test artist without profile photo
            $artist = Artist::factory()->create();

            // Act
            $response = $this->deleteJson(route('admin.v1.artists.profile-photo.delete', $artist));

            // Assert
            $response->assertStatus(404)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', 'No profile photo found');
        });
    });
});
