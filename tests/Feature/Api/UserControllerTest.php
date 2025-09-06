<?php

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
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

describe('User API', function () {
    describe('index', function () {
        test('superadmin can view all users', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test users
            User::factory()->count(20)->create();

            // Act - Get the first page with 10 users per page
            $response = $this->getJson(route('v1.users.index', ['page' => 1, 'per_page' => 10]));

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
        test('superadmin can create user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->postJson(route('v1.users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            // Assert
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                    'profile_photo',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'restored_at',
                ])
                ->assertJsonPath('name', 'Test User')
                ->assertJsonPath('email', 'test@example.com');

            // Verify user was created in database
            $this->assertDatabaseHas('users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        });

        test('user creation requires name, email, and password', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->postJson(route('v1.users.store'), []);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        test('user creation validates email uniqueness', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create existing user
            User::factory()->create(['email' => 'existing@example.com']);

            // Act
            $response = $this->postJson(route('v1.users.store'), [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('show', function () {
        test('superadmin can view any user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user
            $user = User::factory()->create();

            // Act
            $response = $this->getJson(route('v1.users.show', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                    'profile_photo',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'restored_at',
                ])
                ->assertJsonPath('id', $user->id)
                ->assertJsonPath('name', $user->name)
                ->assertJsonPath('email', $user->email);
        });
    });

    describe('update', function () {
        test('superadmin can update any user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user
            $user = User::factory()->create(['name' => 'Original Name']);

            // Act
            $response = $this->putJson(route('v1.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('name', 'Updated Name')
                ->assertJsonPath('email', 'updated@example.com');

            // Verify user was updated in database
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);
        });

        test('user update validates email uniqueness', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create existing users
            $user1 = User::factory()->create(['email' => 'existing@example.com']);
            $user2 = User::factory()->create(['email' => 'other@example.com']);

            // Act
            $response = $this->putJson(route('v1.users.update', $user2), [
                'name' => 'Updated Name',
                'email' => 'existing@example.com',
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('destroy', function () {
        test('superadmin can delete any user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user
            $user = User::factory()->create();

            // Act
            $response = $this->deleteJson(route('v1.users.destroy', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'User deleted');

            // Verify user was soft deleted
            $this->assertSoftDeleted('users', ['id' => $user->id]);
        });
    });

    describe('restore', function () {
        test('superadmin can restore deleted user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create and delete test user
            $user = User::factory()->create();
            $user->delete();

            // Act
            $response = $this->postJson(route('v1.users.restore', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'User restored')
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ]);

            // Verify user was restored
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'deleted_at' => null,
            ]);
        });
    });

    describe('forceDelete', function () {
        test('superadmin can force delete user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create and delete test user
            $user = User::factory()->create();
            $user->delete();

            // Act
            $response = $this->deleteJson(route('v1.users.force-delete', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'User permanently deleted');

            // Verify user was permanently deleted
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        });
    });

    describe('removeProfilePhoto', function () {
        test('superadmin can remove user profile photo', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user with profile photo
            $user = User::factory()->create();

            // Create a fake image file for testing
            $fakeFile = UploadedFile::fake()->image('test-photo.jpg', 100, 100);
            $fakeFile->storeAs('temp', 'test-photo.jpg', 'public');

            $user->addMediaFromDisk('temp/test-photo.jpg', 'public')
                ->toMediaCollection('profile_photos');

            // Act
            $response = $this->deleteJson(route('v1.users.profile-photo.delete', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'Profile photo removed successfully');

            // Verify profile photo was removed
            expect($user->fresh()->getFirstMedia('profile_photos'))->toBeNull();

            // Clean up the test file
            Storage::disk('public')->delete('temp/test-photo.jpg');
        });

        test('returns 404 when no profile photo exists', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user without profile photo
            $user = User::factory()->create();

            // Act
            $response = $this->deleteJson(route('v1.users.profile-photo.delete', $user));

            // Assert
            $response->assertStatus(404)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', 'No profile photo found');
        });
    });

    describe('sendPasswordResetLink', function () {
        test('superadmin can send password reset link to user', function () {
            // Act as superadmin
            $superadmin = TestHelper::createTestSuperAdmin();
            $this->actingAs($superadmin, GuardEnum::ADMIN->value);

            // Create test user
            $user = User::factory()->create();

            // Act
            $response = $this->postJson(route('v1.users.send-password-reset', $user));

            // Assert
            $response->assertStatus(200)
                ->assertJsonPath('message', 'Password reset link sent successfully')
                ->assertJsonPath('status', 'success');
        });
    });
});
