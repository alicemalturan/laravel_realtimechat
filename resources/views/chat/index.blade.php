<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Realtime Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>
    <style>
        .fade-slide-enter-active,.fade-slide-leave-active{transition:all .2s ease}
        .fade-slide-enter-from{opacity:0;transform:translateY(10px)}
        .fade-slide-leave-to{opacity:0;transform:translateY(-10px)}
        .pulse-dot{animation:pulse 1.3s infinite}
        @keyframes pulse {0%,100%{opacity:1}50%{opacity:.3}}
    </style>
</head>
<body class="h-screen bg-slate-950 text-slate-100">
<div id="chat-app" class="flex h-full"></div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            me: @json($me),
            rooms: @json($rooms),
            activeRoom: {{ $activeRoomId }},
            messages: [],
            nextCursor: null,
            loading: true,
            draft: '',
            file: null,
            typers: [],
            online: [],
            echo: null,
            isSending: false,
        }
    },
    computed: {
        typingText() {
            return this.typers.length ? `${this.typers.join(', ')} is typing...` : '';
        },
    },
    mounted() {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        this.setupEcho();
        this.fetchMessages();
        this.pingPresence();
        setInterval(this.pingPresence, 20000);
    },
    methods: {
        setupEcho() {
            this.echo = new window.Echo({
                broadcaster: 'pusher',
                key: '{{ env('PUSHER_APP_KEY', 'app-key') }}',
                wsHost: '{{ env('PUSHER_HOST', '127.0.0.1') }}',
                wsPort: {{ (int) env('PUSHER_PORT', 6001) }},
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
            });
            this.subscribeRoom();
        },
        subscribeRoom() {
            this.echo.private(`chat.room.${this.activeRoom}`)
                .listen('.message.sent', (e) => {
                    this.messages.push(e.message);
                    this.scrollBottom();
                })
                .listen('.typing.updated', (e) => {
                    this.typers = e.typing
                        ? [...new Set([...this.typers, e.user.username])]
                        : this.typers.filter((name) => name !== e.user.username);
                })
                .listen('.presence.updated', (e) => {
                    this.online = e.online
                        ? [...new Set([...this.online, e.user.username])]
                        : this.online.filter((name) => name !== e.user.username);
                })
                .listen('.message.read', (e) => {
                    const target = this.messages.find((m) => m.id === e.messageId);
                    if (target) target.read_status = 'seen';
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
            this.messages
                .filter((m) => m.user.id !== this.me.id)
                .forEach((m) => axios.post(`/messages/${m.id}/read`));
        },
        async sendMessage() {
            if (this.isSending || (!this.draft.trim() && !this.file)) return;

            this.isSending = true;
            const optimistic = {
                id: Date.now(),
                body: this.draft,
                user: this.me,
                meta: this.file ? { attachment: { name: this.file.name, url: '#', mime: this.file.type } } : {},
                reactions: {},
                read_status: 'sent'
            };

            this.messages.push(optimistic);
            this.scrollBottom();

            const form = new FormData();
            form.append('body', this.draft);
            if (this.file) form.append('attachment', this.file);

            this.draft = '';
            this.file = null;
            this.setTyping(false);

            try {
                const { data } = await axios.post(`/rooms/${this.activeRoom}/messages`, form);
                this.messages = this.messages.filter((m) => m.id !== optimistic.id);
                this.messages.push(data.message);
            } finally {
                this.isSending = false;
                this.scrollBottom();
            }
        },
        switchRoom(roomId) {
            if (this.echo) this.echo.leave(`private-chat.room.${this.activeRoom}`);
            this.activeRoom = roomId;
            this.messages = [];
            this.typers = [];
            this.online = [];
            this.nextCursor = null;
            this.subscribeRoom();
            this.fetchMessages();
        },
        onMessageScroll(event) {
            if (event.target.scrollTop <= 60 && this.nextCursor) this.fetchMessages(this.nextCursor);
        },
        scrollBottom() {
            this.$nextTick(() => {
                const box = document.getElementById('messages');
                if (box) box.scrollTop = box.scrollHeight;
            });
        },
        setTyping(value) {
            axios.post(`/rooms/${this.activeRoom}/typing`, { typing: value });
        },
        pingPresence() {
            axios.post(`/rooms/${this.activeRoom}/presence`);
        },
        react(messageId, emoji) {
            axios.post(`/messages/${messageId}/reactions`, { emoji });
        },
    },
    template: `
    <aside class="w-80 border-r border-slate-800 p-4 space-y-4 bg-gradient-to-b from-slate-900 to-slate-950">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-lg">Realtime Rooms</h1>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs rounded-full border border-slate-700 px-3 py-1 hover:border-indigo-400">Logout</button>
            </form>
        </div>

        <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
            <button
                v-for="room in rooms"
                :key="room.id"
                @click="switchRoom(room.id)"
                class="w-full text-left rounded-xl px-3 py-2 transition-all duration-200"
                :class="activeRoom===room.id ? 'bg-indigo-500/20 border border-indigo-300/40 shadow-lg shadow-indigo-500/10' : 'bg-slate-800/50 hover:bg-slate-800'"
            >
                <p class="font-medium">@{{ room.name }}</p>
                <p class="text-xs text-slate-400">@{{ room.users_count }} members</p>
            </button>
        </div>

        <form method="post" action="{{ route('rooms.store') }}" class="space-y-2 rounded-xl border border-slate-800 p-3 bg-slate-900/60">
            @csrf
            <input name="name" required placeholder="Create room" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm"/>
            <select name="type" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <option value="public">Public</option>
                <option value="private">Private</option>
            </select>
            <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 py-2 text-sm">Create</button>
        </form>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="border-b border-slate-800 px-6 py-3 flex justify-between items-center bg-slate-950/95 backdrop-blur">
            <div class="font-semibold">Room #@{{ activeRoom }}</div>
            <div class="text-sm text-emerald-400 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-400 pulse-dot"></span>@{{ online.length }} online
            </div>
        </header>

        <section id="messages" @scroll.passive="onMessageScroll" class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
            <div v-if="loading" class="space-y-3 animate-pulse">
                <div class="h-16 rounded-lg bg-slate-800"></div>
                <div class="h-16 rounded-lg bg-slate-800"></div>
            </div>

            <transition-group name="fade-slide" tag="div" class="space-y-3">
                <article v-for="message in messages" :key="message.id" class="max-w-2xl" :class="message.user.id === me.id ? 'ml-auto text-right' : ''">
                    <div class="inline-block rounded-2xl px-4 py-2" :class="message.user.id === me.id ? 'bg-indigo-500/30' : 'bg-slate-800'">
                        <p class="text-xs text-slate-400">@{{ message.user.username }}</p>
                        <p class="leading-relaxed">@{{ message.body }}</p>
                        <a v-if="message.meta && message.meta.attachment" :href="message.meta.attachment.url" target="_blank" class="text-xs text-indigo-300 underline">@{{ message.meta.attachment.name }}</a>
                        <div class="mt-1 text-xs text-slate-500">@{{ message.read_status }}</div>
                        <div class="mt-1 flex gap-1" :class="message.user.id===me.id ? 'justify-end' : ''">
                            <button v-for="(count, emoji) in message.reactions" :key="emoji" @click="react(message.id, emoji)" class="text-xs bg-slate-700 rounded px-2 py-1">@{{ emoji }} @{{ count }}</button>
                            <button @click="react(message.id, '👍')" class="text-xs">👍</button>
                        </div>
                    </div>
                </article>
            </transition-group>

            <div class="text-sm text-slate-400">@{{ typingText }}</div>
        </section>

        <form @submit.prevent="sendMessage" class="border-t border-slate-800 p-4 sticky bottom-0 bg-slate-950/95 backdrop-blur">
            <div class="flex gap-2 items-center">
                <input
                    @input="setTyping(true)"
                    @blur="setTyping(false)"
                    v-model="draft"
                    placeholder="Type your message..."
                    class="flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 focus:border-indigo-400 outline-none"
                />
                <input type="file" @change="file = $event.target.files[0] ?? null" class="text-xs w-44"/>
                <button :disabled="isSending" class="rounded-xl bg-indigo-500 hover:bg-indigo-400 disabled:opacity-60 px-5 py-3">@{{ isSending ? '...' : 'Send' }}</button>
            </div>
        </form>
    </main>
    `,
}).mount('#chat-app');
</script>
</body>
</html>
