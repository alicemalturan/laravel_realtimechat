# Laravel Realtime Chat (Modernized)

This branch modernizes the app toward a **Laravel 11 + Reverb + Vue + Tailwind** stack.

## What's improved

- ✅ Vue 3-based animated chat UI (rooms sidebar, smooth message transitions, sticky composer).
- ✅ Realtime features: typing, presence heartbeat, reactions, read receipts, optimistic updates.
- ✅ Security hardening: strict input validation, message sanitization, upload filename hardening, security headers middleware, and route throttling.
- ✅ Backend design for multi-room/private chat with indexed tables and cursor pagination.
- ✅ Build stack migration plan from Mix to Vite + Vue.

## Target stack

- **Backend:** Laravel 11
- **Realtime:** Laravel Reverb (Pusher protocol compatible)
- **Frontend:** Vue 3
- **Styling:** Tailwind CSS
- **Uploads:** S3/compatible object store (local disk supported)
- **Performance:** Octane + Redis queues + indexed queries

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Configure Reverb/Pusher-compatible environment variables before running realtime events.

For detailed architecture and deployment notes, see `docs/modern-chat-architecture.md`.
