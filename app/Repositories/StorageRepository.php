<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

class StorageRepository
{
    /**
     * Check if a file exists in local storage
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('local')->exists($path);
    }

    /**
     * Delete a file from local storage
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk('local')->delete($path);
    }

    /**
     * Get all files in a directory
     */
    public function getFiles(string $path): array
    {
        return Storage::disk('local')->files($path);
    }

    /**
     * Get all directories in a path
     */
    public function getDirectories(string $path): array
    {
        return Storage::disk('local')->directories($path);
    }

    /**
     * Delete a directory and all its contents
     */
    public function deleteDirectory(string $path): bool
    {
        return Storage::disk('local')->deleteDirectory($path);
    }
}
