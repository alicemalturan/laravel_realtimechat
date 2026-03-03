<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $roomId;
    public array $user;
    public bool $typing;

    public function __construct(int $roomId, User $user, bool $typing)
    {
        $this->roomId = $roomId;
        $this->user = ['id' => $user->id, 'username' => $user->username];
        $this->typing = $typing;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'typing.updated';
    }
}
