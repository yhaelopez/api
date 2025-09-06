<?php

namespace App\Services;

use App\Cache\ArtistCache;
use App\Exceptions\ForceDeleteActiveRecordException;
use App\Models\Artist;
use App\Repositories\ArtistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ArtistService
{
    public function __construct(
        private ArtistCache $artistCache,
        private ArtistRepository $artistRepository,
        private LoggerService $logger,
        private StorageService $storageService,
        private InAppNotificationService $inAppNotificationService
    ) {}

    /**
     * Get paginated list of artists with caching
     */
    public function getArtistsList(int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->artistCache->rememberList($page, $perPage, $filters, function () use ($page, $perPage, $filters) {
            return $this->artistRepository->paginate($page, $perPage, $filters);
        });
    }

    /**
     * Get a single artist with caching
     */
    public function getArtist(int $id): Artist
    {
        return $this->artistCache->remember($id, function () use ($id) {
            return $this->artistRepository->findWithOwner($id);
        });
    }

    /**
     * Create a new artist
     */
    public function createArtist(array $data): Artist
    {
        $artist = $this->artistRepository->create($data);

        $this->logger->artists()->info('Artist created', [
            'artist_id' => $artist->id,
            'action' => 'artist_created_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Artist Created',
            "Artist '{$artist->name}' has been created successfully."
        );

        return $artist;
    }

    /**
     * Update an existing artist
     */
    public function updateArtist(Artist $artist, array $data): Artist
    {
        $updatedArtist = $this->artistRepository->update($artist, $data);

        $this->logger->artists()->info('Artist updated', [
            'artist_id' => $artist->id,
            'action' => 'artist_updated_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Artist Updated',
            "Artist '{$updatedArtist->name}' has been updated successfully."
        );

        return $updatedArtist;
    }

    /**
     * Delete an artist (soft delete)
     */
    public function deleteArtist(Artist $artist): bool
    {
        $artistName = $artist->name;

        $this->artistRepository->delete($artist);

        $this->logger->artists()->info('Artist soft deleted', [
            'artist_id' => $artist->id,
            'action' => 'artist_soft_deleted_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Artist Deleted',
            "Artist '{$artistName}' has been moved to trash."
        );

        return true;
    }

    /**
     * Add profile photo to existing artist
     */
    public function addProfilePhoto(Artist $artist, UploadedFile $profilePhoto): void
    {
        // Clear existing profile photo (single file collection)
        $artist->clearMediaCollection('profile_photos');

        // Add new profile photo
        $artist->addMedia(
            file: $profilePhoto
        )
            ->usingFileName(
                fileName: $this->storageService->generateProfilePhotoFilename($profilePhoto)
            )
            ->toMediaCollection(
                collectionName: 'profile_photos',
                diskName: $this->storageService->getProfilePhotoDisk()
            );

        $this->logger->artists()->info('Profile photo added to artist', [
            'artist_id' => $artist->id,
            'filename' => $profilePhoto->getClientOriginalName(),
            'action' => 'profile_photo_added',
        ]);
    }

    /**
     * Remove profile photo from artist
     */
    public function removeProfilePhoto(Artist $artist): bool
    {
        if (! $artist->hasMedia('profile_photos')) {
            // Send warning notification to current user
            $this->inAppNotificationService->warning(
                'No Profile Photo',
                'This artist does not have a profile photo to remove.'
            );

            return false;
        }

        // Clear the profile photos collection (removes files and database records)
        $artist->clearMediaCollection('profile_photos');

        $this->logger->artists()->info('Profile photo removed from artist', [
            'artist_id' => $artist->id,
            'action' => 'profile_photo_removed',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Profile Photo Removed',
            "Profile photo for '{$artist->name}' has been removed successfully."
        );

        return true;
    }

    /**
     * Restore a soft-deleted artist
     */
    public function restoreArtist(Artist $artist): Artist
    {
        $restoredArtist = $this->artistRepository->restoreWithOwner($artist);

        $this->logger->artists()->info('Artist restored', [
            'artist_id' => $artist->id,
            'action' => 'artist_restored_success',
        ]);

        // Send success notification to current user
        $this->inAppNotificationService->success(
            'Artist Restored',
            "Artist '{$restoredArtist->name}' has been restored successfully."
        );

        return $restoredArtist;
    }

    /**
     * Force delete an artist permanently
     *
     * @throws ForceDeleteActiveRecordException When attempting to force delete an active artist
     */
    public function forceDeleteArtist(Artist $artist): bool
    {
        // Check if artist is soft-deleted before force deleting
        if (! $artist->trashed()) {
            $this->logger->artists()->alert('Attempted to force delete active artist', [
                'artist_id' => $artist->id,
                'artist_name' => $artist->name,
                'action' => 'force_delete_active_artist_attempt',
            ]);

            throw new ForceDeleteActiveRecordException(
                modelClass: Artist::class,
                modelId: $artist->id
            );
        }

        $artistName = $artist->name;
        $this->artistRepository->forceDelete($artist);

        $this->logger->artists()->info('Artist permanently deleted', [
            'artist_id' => $artist->id,
            'action' => 'artist_permanently_deleted_success',
        ]);

        // Send warning notification to current user
        $this->inAppNotificationService->warning(
            'Artist Permanently Deleted',
            "Artist '{$artistName}' has been permanently deleted and cannot be recovered."
        );

        return true;
    }
}
