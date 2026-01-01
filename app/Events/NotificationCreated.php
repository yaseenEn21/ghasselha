<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    public function broadcastOn(): Channel|array
    {
        $channel = config('services.pusher.channel', 'dashboard.notifications');

        return new Channel($channel);
    }

    public function broadcastAs(): string
    {
        return config('services.pusher.event', 'product.created');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'data' => $this->notification->data,
            'is_read' => $this->notification->is_read,
            'created_at' => optional($this->notification->created_at)->toIso8601String(),
            'created_at_human' => optional($this->notification->created_at)->format('Y-m-d H:i'),
        ];
    }
}
