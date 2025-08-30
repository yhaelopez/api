<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StorageService
{
    public function __construct(
        private LoggerService $logger
    ) {}

    /**
     * Generate a unique filename for profile photo
     */
    public function generateProfilePhotoFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueId = Str::uuid();

        return "profile_{$uniqueId}.{$extension}";
    }

    /**
     * Get disk for profile photos
     */
    public function getProfilePhotoDisk(): string
    {
        return 'public';
    }
}
