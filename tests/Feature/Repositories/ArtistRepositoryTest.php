<?php

use App\Models\Artist;
use App\Models\User;
use App\Repositories\ArtistRepository;
use App\Services\FilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

describe('ArtistRepository', function () {
    beforeEach(function () {
        $this->repository = new ArtistRepository(new FilterService);
    });

    describe('create', function () {
        test('creates artist with valid data', function () {
            // Arrange
            $user = User::factory()->create();
            $data = [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
                'owner_id' => $user->id,
            ];

            // Act
            $artist = $this->repository->create($data);

            // Assert
            expect($artist)->toBeInstanceOf(Artist::class);
            expect($artist->name)->toBe('Test Artist');
            expect($artist->spotify_id)->toBe('test-spotify-id');
            expect($artist->owner_id)->toBe($user->id);
        });

        test('creates artist without owner', function () {
            // Arrange
            $data = [
                'name' => 'Test Artist',
                'spotify_id' => 'test-spotify-id',
                'owner_id' => null,
            ];

            // Act
            $artist = $this->repository->create($data);

            // Assert
            expect($artist)->toBeInstanceOf(Artist::class);
            expect($artist->name)->toBe('Test Artist');
            expect($artist->spotify_id)->toBe('test-spotify-id');
            expect($artist->owner_id)->toBeNull();
        });
    });

    describe('update', function () {
        test('updates artist with valid data', function () {
            // Arrange
            $artist = Artist::factory()->create(['name' => 'Original Name']);
            $user = User::factory()->create();
            $data = [
                'name' => 'Updated Name',
                'spotify_id' => 'updated-spotify-id',
                'owner_id' => $user->id,
            ];

            // Act
            $updatedArtist = $this->repository->update($artist, $data);

            // Assert
            expect($updatedArtist->name)->toBe('Updated Name');
            expect($updatedArtist->spotify_id)->toBe('updated-spotify-id');
            expect($updatedArtist->owner_id)->toBe($user->id);
        });

        test('updates artist without changing owner', function () {
            // Arrange
            $artist = Artist::factory()->create(['name' => 'Original Name']);
            $data = [
                'name' => 'Updated Name',
            ];

            // Act
            $updatedArtist = $this->repository->update($artist, $data);

            // Assert
            expect($updatedArtist->name)->toBe('Updated Name');
            expect($updatedArtist->owner_id)->toBe($artist->owner_id);
        });
    });

    describe('delete', function () {
        test('soft deletes artist', function () {
            // Arrange
            $artist = Artist::factory()->create();

            // Act
            $this->repository->delete($artist);

            $artist->fresh();

            // Assert
            expect($artist->trashed())->toBeTrue();
        });
    });

    describe('restore', function () {
        test('restores soft deleted artist', function () {
            // Arrange
            $artist = Artist::factory()->create();
            $artist->delete();

            // Act
            $restoredArtist = $this->repository->restoreWithOwner($artist);

            // Assert
            expect($restoredArtist->trashed())->toBeFalse();
            expect($restoredArtist->id)->toBe($artist->id);
        });
    });

    describe('forceDelete', function () {
        test('permanently deletes artist', function () {
            // Arrange
            $artist = Artist::factory()->create();
            $artistId = $artist->id;

            // Act
            $this->repository->forceDelete($artist);

            // Assert
            expect(Artist::withTrashed()->find($artistId))->toBeNull();
        });
    });

    describe('find', function () {
        test('finds artist by id', function () {
            // Arrange
            $artist = Artist::factory()->create();

            // Act
            $foundArtist = $this->repository->find($artist->id);

            // Assert
            expect($foundArtist)->toBeInstanceOf(Artist::class);
            expect($foundArtist->id)->toBe($artist->id);
        });

        test('returns null for non-existent artist', function () {
            // Act
            $foundArtist = $this->repository->find(999);

            // Assert
            expect($foundArtist)->toBeNull();
        });
    });

    describe('findWithTrashed', function () {
        test('finds soft deleted artist', function () {
            // Arrange
            $artist = Artist::factory()->create();
            $artist->delete();

            // Act
            $foundArtist = $this->repository->findWithTrashed($artist->id);

            // Assert
            expect($foundArtist)->toBeInstanceOf(Artist::class);
            expect($foundArtist->id)->toBe($artist->id);
            expect($foundArtist->trashed())->toBeTrue();
        });
    });
});
