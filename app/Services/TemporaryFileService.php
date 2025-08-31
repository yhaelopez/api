<?php

namespace App\Services;

use App\Models\TemporaryFile;
use App\Repositories\StorageRepository;
use App\Repositories\TemporaryFileRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemporaryFileService
{
    public function __construct(
        private StorageRepository $storageRepository,
        private TemporaryFileRepository $temporaryFileRepository
    ) {}

    /**
     * Store a temporary file and return the folder name
     */
    public function storeTemporaryFile(UploadedFile $file): string
    {
        $folder = Str::uuid()->toString();
        $filename = $this->generateTemporaryFilename($file);

        // Store the file in temporary storage
        $file->storeAs("tmp/{$folder}", $filename, 'local');

        // Create temporary file record
        $this->temporaryFileRepository->create([
            'folder' => $folder,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'expires_at' => now()->addHours(24), // Expire in 24 hours
        ]);

        return $folder;
    }

    /**
     * Move temporary files to Media Library and clean up
     */
    public function moveTemporaryFilesToMedia(string $folder, string $collectionName, $model): array
    {
        $temporaryFiles = $this->temporaryFileRepository->findByFolder($folder);
        $movedFiles = [];
        $errors = [];

        foreach ($temporaryFiles as $tempFile) {
            try {
                // Check if file still exists
                if (! $this->storageRepository->fileExists($tempFile->getRelativePath())) {
                    $errors[] = "File {$tempFile->filename} not found";

                    continue;
                }

                // Add media to the model with custom filename
                $media = $model->addMediaFromDisk($tempFile->getRelativePath(), 'local')
                    ->usingName($tempFile->original_name)
                    ->usingFileName('profile_photo_'.time().'.'.pathinfo($tempFile->filename, PATHINFO_EXTENSION))
                    ->toMediaCollection($collectionName);

                $movedFiles[] = [
                    'id' => $media->id,
                    'filename' => $tempFile->filename,
                    'original_name' => $tempFile->original_name,
                ];

                // Clean up the temporary file
                $this->cleanupTemporaryFile($tempFile);

            } catch (Exception $e) {
                $errors[] = "Failed to move {$tempFile->filename}: ".$e->getMessage();
            }
        }

        return [
            'moved_files' => $movedFiles,
            'errors' => $errors,
        ];
    }

    /**
     * Simple method to move temporary files to media library (for UserController)
     */
    public function moveTempToMedia(string $folder, string $collectionName, Model $model): bool
    {
        // Only clear existing media if there are items in the collection
        if ($model->hasMedia($collectionName)) {
            $model->clearMediaCollection($collectionName);
        }

        $result = $this->moveTemporaryFilesToMedia($folder, $collectionName, $model);

        return ! empty($result['moved_files']);
    }

    /**
     * Clean up expired temporary files
     */
    public function cleanupExpiredTemporaryFiles(): int
    {
        $expiredFiles = $this->temporaryFileRepository->getExpired();
        $cleanedCount = 0;

        foreach ($expiredFiles as $tempFile) {
            if ($this->cleanupTemporaryFile($tempFile)) {
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }

    /**
     * Clean up empty tmp folders
     */
    public function cleanupEmptyTmpFolders(): int
    {
        $tmpPath = 'tmp';
        $removedCount = 0;

        if (! $this->storageRepository->fileExists($tmpPath)) {
            return $removedCount;
        }

        $directories = $this->storageRepository->getDirectories($tmpPath);

        foreach ($directories as $directory) {
            $files = $this->storageRepository->getFiles($directory);

            if (empty($files)) {
                $this->storageRepository->deleteDirectory($directory);
                $removedCount++;
            }
        }

        return $removedCount;
    }

    /**
     * Generate a unique filename for temporary storage
     */
    private function generateTemporaryFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueId = Str::uuid();

        return "temp_{$uniqueId}.{$extension}";
    }

    /**
     * Clean up a temporary file and its record
     */
    private function cleanupTemporaryFile(TemporaryFile $tempFile): bool
    {
        try {
            // Delete the physical file
            if ($this->storageRepository->fileExists($tempFile->getRelativePath())) {
                $this->storageRepository->deleteFile($tempFile->getRelativePath());
            }

            // Try to remove the folder if it's empty
            $folderPath = "tmp/{$tempFile->folder}";
            if ($this->storageRepository->fileExists($folderPath)) {
                $files = $this->storageRepository->getFiles($folderPath);
                if (empty($files)) {
                    $this->storageRepository->deleteDirectory($folderPath);
                }
            }

            // Delete the database record
            $this->temporaryFileRepository->delete($tempFile);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to cleanup temporary file', [
                'file_id' => $tempFile->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
