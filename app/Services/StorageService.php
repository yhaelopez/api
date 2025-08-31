<?php

namespace App\Services;

use App\Repositories\FileRepository;
use Illuminate\Http\UploadedFile;

class StorageService
{
    public function __construct(
        private LoggerService $logger,
        private FileRepository $fileRepository
    ) {}

    /**
     * Generate a unique filename for profile photo
     */
    public function generateProfilePhotoFilename(UploadedFile $file): string
    {
        return $this->fileRepository->generateProfilePhotoFilename($file);
    }

    /**
     * Get disk for profile photos
     */
    public function getProfilePhotoDisk(): string
    {
        return 'public';
    }

    /**
     * Get custom media collection path for profile photos
     */
    public function getProfilePhotoPath(): string
    {
        return 'users/{id}/profile_photo';
    }
}
