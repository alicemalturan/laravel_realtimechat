<?php

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return ChatRoom::whereKey($roomId)
        ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
        ->exists();
});
