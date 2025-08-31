<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileRepository
{
    /**
     * Generate a unique filename with optional prefix
     */
    public function generateUniqueFilename(UploadedFile $file, string $prefix = 'file'): string
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueId = Str::uuid();

        return "{$prefix}_{$uniqueId}.{$extension}";
    }

    /**
     * Get file extension from uploaded file
     */
    public function getFileExtension(UploadedFile $file): string
    {
        return $file->getClientOriginalExtension();
    }

    /**
     * Create a unique path for file storage
     */
    public function createUniquePath(string $basePath, string $filename): string
    {
        return "{$basePath}/{$filename}";
    }

    /**
     * Generate filename for profile photos
     */
    public function generateProfilePhotoFilename(UploadedFile $file): string
    {
        return $this->generateUniqueFilename($file, 'profile');
    }
}
