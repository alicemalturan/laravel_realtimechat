<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'created_by'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['last_read_at', 'last_seen_at'])
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
