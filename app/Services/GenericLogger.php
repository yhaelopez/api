<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenericLogger
{
    protected string $channel;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Log with emergency level
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log with alert level
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log with critical level
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log with error level
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log with warning level
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log with notice level
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log with info level
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log with debug level
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Internal logging method
     */
    private function log(string $level, string $message, array $context): void
    {
        $context = $this->enrichContext($context);

        Log::channel($this->channel)->log($level, $message, $context);
    }

    /**
     * Enrich context with common information
     */
    protected function enrichContext(array $context): array
    {
        $request = request();

        return array_merge($context, [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'user_name' => Auth::user()?->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
