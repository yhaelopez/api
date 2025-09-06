<?php

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\FilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class, WithFaker::class);

describe('UserRepository', function () {
    beforeEach(function () {
        $this->repository = new UserRepository(new FilterService);
    });

    describe('create', function () {
        test('creates user with valid data', function () {
            // Arrange
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            // Act
            $user = $this->repository->create($data);

            // Assert
            expect($user)->toBeInstanceOf(User::class);
            expect($user->name)->toBe('Test User');
            expect($user->email)->toBe('test@example.com');
            expect(Hash::check('password123', $user->password))->toBeTrue();
        });

        test('creates user with hashed password', function () {
            // Arrange
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            // Act
            $user = $this->repository->create($data);

            // Assert
            expect($user->password)->not->toBe('password123');
            expect(Hash::check('password123', $user->password))->toBeTrue();
        });
    });

    describe('update', function () {
        test('updates user with valid data', function () {
            // Arrange
            $user = User::factory()->create(['name' => 'Original Name']);
            $data = [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ];

            // Act
            $updatedUser = $this->repository->update($user, $data);

            // Assert
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->email)->toBe('updated@example.com');
        });

        test('updates user password when provided', function () {
            // Arrange
            $user = User::factory()->create();
            $data = [
                'name' => 'Updated Name',
                'password' => 'newpassword123',
            ];

            // Act
            $updatedUser = $this->repository->update($user, $data);

            // Assert
            expect($updatedUser->name)->toBe('Updated Name');
            expect(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();
        });

        test('does not update password when not provided', function () {
            // Arrange
            $user = User::factory()->create();
            $originalPassword = $user->password;
            $data = [
                'name' => 'Updated Name',
            ];

            // Act
            $updatedUser = $this->repository->update($user, $data);

            // Assert
            expect($updatedUser->name)->toBe('Updated Name');
            expect($updatedUser->password)->toBe($originalPassword);
        });
    });

    describe('delete', function () {
        test('soft deletes user', function () {
            // Arrange
            $user = User::factory()->create();

            // Act
            $this->repository->delete($user);

            $user->fresh();

            // Assert
            expect($user->trashed())->toBeTrue();
        });
    });

    describe('restore', function () {
        test('restores soft deleted user', function () {
            // Arrange
            $user = User::factory()->create();
            $user->delete();

            // Act
            $restoredUser = $this->repository->restoreWithRoles($user);

            // Assert
            expect($restoredUser->trashed())->toBeFalse();
            expect($restoredUser->id)->toBe($user->id);
        });
    });

    describe('forceDelete', function () {
        test('permanently deletes user', function () {
            // Arrange
            $user = User::factory()->create();
            $userId = $user->id;

            // Act
            $this->repository->forceDelete($user);

            // Assert
            expect(User::withTrashed()->find($userId))->toBeNull();
        });
    });

    describe('find', function () {
        test('finds user by id', function () {
            // Arrange
            $user = User::factory()->create();

            // Act
            $foundUser = $this->repository->find($user->id);

            // Assert
            expect($foundUser)->toBeInstanceOf(User::class);
            expect($foundUser->id)->toBe($user->id);
        });

        test('returns null for non-existent user', function () {
            // Act
            $foundUser = $this->repository->find(999);

            // Assert
            expect($foundUser)->toBeNull();
        });
    });

    describe('findWithTrashed', function () {
        test('finds soft deleted user', function () {
            // Arrange
            $user = User::factory()->create();
            $user->delete();

            // Act
            $foundUser = $this->repository->findWithTrashed($user->id);

            // Assert
            expect($foundUser)->toBeInstanceOf(User::class);
            expect($foundUser->id)->toBe($user->id);
            expect($foundUser->trashed())->toBeTrue();
        });
    });

    describe('findByEmail', function () {
        test('finds user by email', function () {
            // Arrange
            $user = User::factory()->create(['email' => 'test@example.com']);

            // Act
            $foundUser = $this->repository->findByEmail('test@example.com');

            // Assert
            expect($foundUser)->toBeInstanceOf(User::class);
            expect($foundUser->email)->toBe('test@example.com');
        });

        test('returns null for non-existent email', function () {
            // Act
            $foundUser = $this->repository->findByEmail('nonexistent@example.com');

            // Assert
            expect($foundUser)->toBeNull();
        });
    });
});
