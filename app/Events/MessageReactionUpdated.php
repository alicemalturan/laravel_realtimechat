<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $roomId;
    public int $messageId;

    public function __construct(int $roomId, int $messageId)
    {
        $this->roomId = $roomId;
        $this->messageId = $messageId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'reaction.updated';
    }
}
