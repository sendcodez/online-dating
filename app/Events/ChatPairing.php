<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatPairing implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public string $userId,
        public string $action, // 'paired', 'disconnected', 'message', 'typing'
        public ?string $message = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'chat.event';
    }

    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'action' => $this->action,
            'message' => $this->message,
            'timestamp' => now()->toISOString(),
        ];
    }
}
