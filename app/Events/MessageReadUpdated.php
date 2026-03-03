<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReadUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $roomId;
    public int $messageId;
    public int $userId;

    public function __construct(int $roomId, int $messageId, int $userId)
    {
        $this->roomId = $roomId;
        $this->messageId = $messageId;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'message.read';
    }
}
