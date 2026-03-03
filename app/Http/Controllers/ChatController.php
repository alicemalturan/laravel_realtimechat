<?php

namespace App\Http\Controllers;

use App\Events\MessageReactionUpdated;
use App\Events\MessageReadUpdated;
use App\Events\MessageSent;
use App\Events\PresenceUpdated;
use App\Events\TypingStatusUpdated;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\MessageReadReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $rooms = ChatRoom::withCount('users')
            ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
            ->orderBy('name')
            ->get();

        if ($rooms->isEmpty()) {
            $room = ChatRoom::create([
                'name' => 'General',
                'slug' => 'general',
                'type' => 'public',
                'created_by' => $user->id,
            ]);
            $room->users()->attach($user->id, ['last_seen_at' => now()]);
            $rooms = collect([$room->loadCount('users')]);
        }

        return view('chat.index', [
            'rooms' => $rooms,
            'activeRoomId' => (int) ($request->query('room') ?: $rooms->first()->id),
            'me' => $user->only(['id', 'username']),
        ]);
    }

    public function listMessages(Request $request, ChatRoom $room): JsonResponse
    {
        abort_unless($this->isRoomMember($request, $room), 403);

        $messages = Message::with(['user:id,username', 'reactions:user_id,message_id,emoji'])
            ->where('chat_room_id', $room->id)
            ->latest('id')
            ->cursorPaginate(20)
            ->through(fn (Message $message) => $this->toMessagePayload($message, (int) $request->user()->id));

        return response()->json($messages);
    }

    public function sendMessage(Request $request, ChatRoom $room): JsonResponse
    {
        abort_unless($this->isRoomMember($request, $room), 403);

        $payload = $request->validate([
            'body' => ['nullable', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,zip'],
        ]);

        if (blank($payload['body'] ?? null) && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Message cannot be empty'], 422);
        }

        $meta = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('chat-uploads', now()->format('Y/m') . '/' . Str::uuid() . '-' . $safeName, 'public');
            $meta['attachment'] = [
                'name' => Str::limit($safeName, 120),
                'url' => Storage::disk('public')->url($path),
                'mime' => $file->getMimeType(),
            ];
        }

        $message = Message::create([
            'chat_room_id' => $room->id,
            'user_id' => $request->user()->id,
            'body' => strip_tags($payload['body'] ?? ''),
            'meta' => $meta,
            'delivered_at' => now(),
        ]);

        $message->load('user:id,username', 'reactions:user_id,message_id,emoji');
        $serialized = $this->toMessagePayload($message, (int) $request->user()->id);

        broadcast(new MessageSent($room->id, $serialized))->toOthers();

        return response()->json(['message' => $serialized]);
    }

    public function typing(Request $request, ChatRoom $room)
    {
        abort_unless($this->isRoomMember($request, $room), 403);

        $request->validate(['typing' => ['required', 'boolean']]);

        broadcast(new TypingStatusUpdated($room->id, $request->user(), $request->boolean('typing')))->toOthers();

        return response()->noContent();
    }

    public function presence(Request $request, ChatRoom $room): JsonResponse
    {
        abort_unless($this->isRoomMember($request, $room), 403);

        $room->users()->updateExistingPivot($request->user()->id, ['last_seen_at' => now()]);
        broadcast(new PresenceUpdated($room->id, $request->user(), true))->toOthers();

        return response()->json(['ok' => true]);
    }

    public function addReaction(Request $request, Message $message)
    {
        abort_unless($this->isRoomMember($request, $message->room), 403);

        $payload = $request->validate(['emoji' => ['required', 'string', 'max:16', 'regex:/^\X+$/u']]);

        MessageReaction::firstOrCreate([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji' => $payload['emoji'],
        ]);

        broadcast(new MessageReactionUpdated($message->chat_room_id, $message->id))->toOthers();

        return response()->noContent();
    }

    public function markAsRead(Request $request, Message $message)
    {
        abort_unless($this->isRoomMember($request, $message->room), 403);

        MessageReadReceipt::updateOrCreate(
            ['message_id' => $message->id, 'user_id' => $request->user()->id],
            ['delivered_at' => now(), 'read_at' => now()]
        );

        $message->update(['seen_at' => now()]);
        broadcast(new MessageReadUpdated($message->chat_room_id, $message->id, $request->user()->id))->toOthers();

        return response()->noContent();
    }

    public function createRoom(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:public,private'],
        ]);

        $room = DB::transaction(function () use ($data, $request) {
            $room = ChatRoom::create([
                'name' => strip_tags($data['name']),
                'slug' => Str::slug($data['name']) . '-' . Str::random(6),
                'type' => $data['type'],
                'created_by' => $request->user()->id,
            ]);

            $room->users()->attach($request->user()->id, ['last_seen_at' => now()]);

            return $room;
        });

        return redirect()->route('chat.index', ['room' => $room->id]);
    }

    private function statusForMessage(Message $message, int $userId): string
    {
        if ($message->seen_at) {
            return 'seen';
        }

        if ($message->delivered_at) {
            return 'delivered';
        }

        return $message->user_id === $userId ? 'sent' : 'delivered';
    }

    private function isRoomMember(Request $request, ChatRoom $room): bool
    {
        return $room->users()->where('users.id', $request->user()->id)->exists();
    }

    private function toMessagePayload(Message $message, int $viewerId): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'user' => $message->user,
            'meta' => $message->meta,
            'created_at' => $message->created_at?->toIso8601String(),
            'read_status' => $this->statusForMessage($message, $viewerId),
            'reactions' => $message->reactions->groupBy('emoji')->map->count(),
        ];
    }
}
