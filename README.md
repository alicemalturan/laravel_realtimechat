# Laravel Realtime Chat

Modernized realtime chat baseline with:

- Auth flow (register/login/logout)
- Multi-room messaging
- Typing indicators
- Presence heartbeat (online users)
- Read receipts (sent/delivered/seen)
- Emoji reactions
- File/image attachments
- Cursor pagination + infinite scroll
- Optimistic UI and skeleton loaders

## Run locally

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

If using websockets, configure Pusher-compatible settings (Reverb or Soketi) in `.env`.

See `docs/modern-chat-architecture.md` for stack/deployment decisions.
