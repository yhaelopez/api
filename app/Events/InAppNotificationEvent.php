<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * InAppNotificationEvent
 *
 * Dispatch in-app notifications to users via broadcasting
 *
 * @property User $user
 * @property string $type
 * @property string $title
 * @property string|null $message
 * @property int $duration
 */
class InAppNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;

    private string $type;

    private string $title;

    private ?string $message;

    private int $duration;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $type, string $title, ?string $message = null, int $duration = 5000)
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->duration = $duration;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("user.{$this->user->id}");
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return ! empty($this->user);
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'duration' => $this->duration,
        ];

        if ($this->message) {
            $data['message'] = $this->message;
        }

        return $data;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'in-app-notification',
            'user:'.$this->user->id,
            'type:'.$this->type,
        ];
    }
}
