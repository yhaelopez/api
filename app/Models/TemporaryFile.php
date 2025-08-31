<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemporaryFile extends Model
{
    protected $fillable = [
        'folder',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'size' => 'integer',
    ];

    /**
     * Check if the temporary file has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get the full path to the temporary file
     */
    public function getFullPath(): string
    {
        return storage_path("app/tmp/{$this->folder}/{$this->filename}");
    }

    /**
     * Get the relative path for storage operations
     */
    public function getRelativePath(): string
    {
        return "tmp/{$this->folder}/{$this->filename}";
    }

    /**
     * Scope to get expired temporary files
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}
