<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['chat_room_id', 'user_id', 'body', 'meta', 'delivered_at', 'seen_at'];

    protected $casts = [
        'meta' => 'array',
        'delivered_at' => 'datetime',
        'seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function readReceipts()
    {
        return $this->hasMany(MessageReadReceipt::class);
    }
}
