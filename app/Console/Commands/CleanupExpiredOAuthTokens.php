<?php

namespace App\Console\Commands;

use App\Services\OAuthCredentialService;
use Illuminate\Console\Command;

class CleanupExpiredOAuthTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'oauth:cleanup-expired';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired OAuth tokens';

    public function __construct(
        private OAuthCredentialService $credentialService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting OAuth token cleanup...');

        $cleanedCount = $this->credentialService->cleanupExpiredTokens();

        $this->info("Cleaned up {$cleanedCount} expired OAuth tokens.");

        return Command::SUCCESS;
    }
}
