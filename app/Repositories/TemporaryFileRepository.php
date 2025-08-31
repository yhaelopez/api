<?php

namespace App\Repositories;

use App\Models\TemporaryFile;
use Illuminate\Database\Eloquent\Collection;

class TemporaryFileRepository
{
    /**
     * Create a new temporary file record
     */
    public function create(array $data): TemporaryFile
    {
        return TemporaryFile::create($data);
    }

    /**
     * Find temporary files by folder
     */
    public function findByFolder(string $folder): Collection
    {
        return TemporaryFile::where('folder', $folder)->get();
    }

    /**
     * Get expired temporary files
     */
    public function getExpired(): Collection
    {
        return TemporaryFile::expired()->get();
    }

    /**
     * Delete a temporary file record
     */
    public function delete(TemporaryFile $temporaryFile): bool
    {
        return $temporaryFile->delete();
    }
}
