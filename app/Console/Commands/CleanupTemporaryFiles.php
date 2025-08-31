<?php

namespace App\Console\Commands;

use App\Services\TemporaryFileService;
use Illuminate\Console\Command;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temporary-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired temporary files and empty tmp folders';

    /**
     * Execute the console command.
     */
    public function handle(TemporaryFileService $temporaryFileService)
    {
        $this->info('Starting temporary files cleanup...');

        // Clean up expired temporary files from database
        $cleanedCount = $temporaryFileService->cleanupExpiredTemporaryFiles();
        $this->info("Cleaned up {$cleanedCount} expired temporary files from database");

        // Clean up empty tmp folders
        $removedFolders = $temporaryFileService->cleanupEmptyTmpFolders();
        if ($removedFolders > 0) {
            $this->info("Removed {$removedFolders} empty tmp folders");
        }

        $this->info('Temporary files cleanup completed!');
    }
}
