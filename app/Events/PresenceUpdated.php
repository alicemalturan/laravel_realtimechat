<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresenceUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $roomId;
    public array $user;
    public bool $online;

    public function __construct(int $roomId, User $user, bool $online)
    {
        $this->roomId = $roomId;
        $this->user = ['id' => $user->id, 'username' => $user->username];
        $this->online = $online;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'presence.updated';
    }
}
