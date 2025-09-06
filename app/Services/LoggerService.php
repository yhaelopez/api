<?php

namespace App\Services;

class LoggerService
{
    public function oauth(): GenericLogger
    {
        return new GenericLogger('oauth');
    }

    /**
     * Get user logger instance
     */
    public function users(): GenericLogger
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

    /**
     * Get admin logger instance
     */
    public function admins(): GenericLogger
    {
        return new GenericLogger('admins');
    }
}
