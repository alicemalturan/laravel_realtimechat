<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Realtime Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>
</head>
<body class="h-screen bg-slate-950 text-slate-100" x-data="chatApp()" x-init="init()">
<div class="flex h-full">
    <aside class="w-72 border-r border-slate-800 p-4 space-y-4 bg-slate-900/80">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-lg">Rooms</h1>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="text-xs text-slate-400">Logout</button></form>
        </div>
        <div class="space-y-2 max-h-[60vh] overflow-y-auto">
            @foreach($rooms as $room)
                <button @click="switchRoom({{ $room->id }})" class="w-full text-left rounded-lg px-3 py-2" :class="activeRoom==={{ $room->id }} ? 'bg-indigo-500/20 border border-indigo-400/30':'bg-slate-800/50'">
                    <p class="font-medium">{{ $room->name }}</p>
                    <p class="text-xs text-slate-400">{{ $room->users_count }} members</p>
                </button>
            @endforeach
        </div>
        <form method="post" action="{{ route('rooms.store') }}" class="space-y-2">
            @csrf
            <input name="name" required placeholder="New room" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm"/>
            <select name="type" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm"><option value="public">Public</option><option value="private">Private</option></select>
            <button class="w-full rounded-lg bg-indigo-500 py-2 text-sm">Create room</button>
        </form>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="border-b border-slate-800 px-6 py-3 flex justify-between"><div>Room #<span x-text="activeRoom"></span></div><div class="text-sm text-emerald-400" x-text="onlineText"></div></header>

        <section id="messages" @scroll.passive="onScroll" class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
            <template x-if="loading">
                <div class="space-y-3 animate-pulse"><div class="h-16 rounded-lg bg-slate-800"></div><div class="h-16 rounded-lg bg-slate-800"></div></div>
            </template>
            <template x-for="message in messages" :key="message.id">
                <article class="max-w-2xl" :class="message.user.id === me.id ? 'ml-auto text-right' : ''">
                    <div class="inline-block rounded-2xl px-4 py-2" :class="message.user.id === me.id ? 'bg-indigo-500/30':'bg-slate-800'">
                        <p class="text-xs text-slate-400" x-text="message.user.username"></p>
                        <p x-text="message.body"></p>
                        <template x-if="message.meta && message.meta.attachment">
                            <a :href="message.meta.attachment.url" target="_blank" class="text-xs text-indigo-300 underline" x-text="message.meta.attachment.name"></a>
                        </template>
                        <div class="mt-1 text-xs text-slate-500" x-text="message.read_status"></div>
                        <div class="mt-1 flex gap-1" :class="message.user.id===me.id?'justify-end':''">
                            <template x-for="(count, emoji) in message.reactions"><button @click="react(message.id, emoji)" class="text-xs bg-slate-700 rounded px-1" x-text="emoji + ' ' + count"></button></template>
                            <button @click="react(message.id,'👍')" class="text-xs">👍</button>
                        </div>
                    </div>
                </article>
            </template>
            <div class="text-sm text-slate-400" x-text="typingText"></div>
        </section>

        <form @submit.prevent="send" class="border-t border-slate-800 p-4 sticky bottom-0 bg-slate-950">
            <div class="flex gap-2">
                <input @input.debounce.300ms="setTyping(true)" x-model="draft" placeholder="Write a message..." class="flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3"/>
                <input type="file" @change="pickFile" class="text-xs w-44"/>
                <button class="rounded-xl bg-indigo-500 px-5">Send</button>
            </div>
        </form>
    </main>
</div>

<script>
function chatApp() {
    return {
        me: @json(auth()->user()),
        activeRoom: {{ $activeRoomId }},
        messages: [],
        nextCursor: null,
        loading: true,
        draft: '',
        file: null,
        typers: new Set(),
        online: new Set(),
        echo: null,
        init() {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
            this.bootEcho();
            this.fetchMessages();
            setInterval(() => this.pingPresence(), 20000);
        },
        bootEcho() {
            this.echo = new window.Echo({
                broadcaster: 'pusher',
                key: '{{ env('PUSHER_APP_KEY', 'app-key') }}',
                wsHost: '{{ env('PUSHER_HOST', '127.0.0.1') }}',
                wsPort: {{ (int) env('PUSHER_PORT', 6001) }},
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
            });
            this.subscribe();
        },
        subscribe() {
            this.echo.private(`chat.room.${this.activeRoom}`)
                .listen('.message.sent', (e) => { this.messages.push(e.message); this.scrollBottom(); })
                .listen('.typing.updated', (e) => { e.typing ? this.typers.add(e.user.username) : this.typers.delete(e.user.username); })
                .listen('.presence.updated', (e) => { e.online ? this.online.add(e.user.username) : this.online.delete(e.user.username); })
                .listen('.message.read', (e) => {
                    const msg = this.messages.find(m => m.id === e.messageId);
                    if (msg) msg.read_status = 'seen';
                });
        },
        async fetchMessages(cursor = null) {
            this.loading = !cursor;
            const { data } = await axios.get(`/rooms/${this.activeRoom}/messages`, { params: cursor ? { cursor } : {} });
            const incoming = data.data.reverse();
            this.messages = cursor ? [...incoming, ...this.messages] : incoming;
            this.nextCursor = data.next_cursor;
            this.loading = false;
            if (!cursor) this.scrollBottom();
            this.messages.filter(m => m.user.id !== this.me.id).forEach(m => axios.post(`/messages/${m.id}/read`));
        },
        async send() {
            const optimistic = { id: Date.now(), body: this.draft, user: this.me, meta: {}, reactions: {}, read_status: 'sent' };
            this.messages.push(optimistic);
            this.scrollBottom();
            const form = new FormData();
            form.append('body', this.draft);
            if (this.file) form.append('attachment', this.file);
            this.draft = '';
            this.file = null;
            this.setTyping(false);
            const { data } = await axios.post(`/rooms/${this.activeRoom}/messages`, form);
            this.messages = this.messages.filter(m => m.id !== optimistic.id);
            this.messages.push(data.message);
            this.scrollBottom();
        },
        switchRoom(roomId) {
            if (this.echo) this.echo.leave(`private-chat.room.${this.activeRoom}`);
            this.activeRoom = roomId;
            this.messages = [];
            this.typers = new Set();
            this.online = new Set();
            this.subscribe();
            this.fetchMessages();
        },
        onScroll(event) {
            if (event.target.scrollTop <= 60 && this.nextCursor) this.fetchMessages(this.nextCursor);
        },
        scrollBottom() {
            this.$nextTick(() => { const box = document.getElementById('messages'); box.scrollTop = box.scrollHeight; });
        },
        pickFile(event) { this.file = event.target.files[0] ?? null; },
        setTyping(value) { axios.post(`/rooms/${this.activeRoom}/typing`, { typing: value }); },
        pingPresence() { axios.post(`/rooms/${this.activeRoom}/presence`); },
        react(messageId, emoji) { axios.post(`/messages/${messageId}/reactions`, { emoji }); },
        get typingText() { return this.typers.size ? `${Array.from(this.typers).join(', ')} is typing...` : ''; },
        get onlineText() { return `${this.online.size} online`; },
    }
}
</script>
</body>
</html>
