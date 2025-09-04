<?php

namespace App\Services;

class LoggerService
{
    /**
     * Get user logger instance
     */
    public function user(): GenericLogger
    {
        return new GenericLogger('users');
    }

    /**
     * Get artist logger instance
     */
    public function artists(): GenericLogger
    {
        return new GenericLogger('artists');
    }
}
