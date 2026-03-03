<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $roomId;
    public array $message;

    public function __construct(int $roomId, Message $message)
    {
        $this->roomId = $roomId;
        $this->message = [
            'id' => $message->id,
            'body' => $message->body,
            'meta' => $message->meta,
            'created_at' => optional($message->created_at)->toIso8601String(),
            'user' => ['id' => $message->user->id, 'username' => $message->user->username],
            'reactions' => [],
            'read_status' => 'sent',
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
