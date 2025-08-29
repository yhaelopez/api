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
}
