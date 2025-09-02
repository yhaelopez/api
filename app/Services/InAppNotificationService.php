<?php

namespace App\Services;

use App\Events\InAppNotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InAppNotificationService
{
    public function __construct(
        private LoggerService $logger
    ) {}

    /**
     * Dispatch a success notification to the authenticated user
     */
    public function success(string $title, ?string $message = null): void
    {
        $this->dispatch('success', $title, $message);
    }

    /**
     * Dispatch an error notification to the authenticated user
     */
    public function error(string $title, ?string $message = null): void
    {
        $this->dispatch('error', $title, $message, 8000);
    }

    /**
     * Dispatch a warning notification to the authenticated user
     */
    public function warning(string $title, ?string $message = null): void
    {
        $this->dispatch('warning', $title, $message, 6000);
    }

    /**
     * Dispatch an info notification to the authenticated user
     */
    public function info(string $title, ?string $message = null): void
    {
        $this->dispatch('info', $title, $message);
    }

    /**
     * Dispatch a success notification to a specific user
     */
    public function successTo(User $user, string $title, ?string $message = null): void
    {
        $this->dispatchTo($user, 'success', $title, $message);
    }

    /**
     * Dispatch an error notification to a specific user
     */
    public function errorTo(User $user, string $title, ?string $message = null): void
    {
        $this->dispatchTo($user, 'error', $title, $message, 8000);
    }

    /**
     * Dispatch a warning notification to a specific user
     */
    public function warningTo(User $user, string $title, ?string $message = null): void
    {
        $this->dispatchTo($user, 'warning', $title, $message, 6000);
    }

    /**
     * Dispatch an info notification to a specific user
     */
    public function infoTo(User $user, string $title, ?string $message = null): void
    {
        $this->dispatchTo($user, 'info', $title, $message);
    }

    /**
     * Dispatch notification event to the authenticated user
     */
    private function dispatch(string $type, string $title, ?string $message = null, int $duration = 5000): void
    {
        $user = Auth::user();

        $this->logger->user()->info('InAppNotificationService: Dispatching notification', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);

        if (! $user) {
            return; // No authenticated user, skip notification
        }

        InAppNotificationEvent::dispatch($user, $type, $title, $message, $duration);
    }

    /**
     * Dispatch notification event to a specific user
     */
    private function dispatchTo(User $user, string $type, string $title, ?string $message = null, int $duration = 5000): void
    {
        InAppNotificationEvent::dispatch($user, $type, $title, $message, $duration);
    }
}
