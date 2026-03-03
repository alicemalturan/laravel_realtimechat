# Modern Realtime Chat Stack (Laravel)

## Stack Decision

- **Backend:** Laravel 11 target (this repository currently demonstrates the architecture on Laravel 8 codebase).
- **Realtime transport:** **Laravel Reverb** (primary) or **Soketi** (drop-in Pusher protocol alternative).
- **Frontend:** Vue 3 + Inertia or React + Inertia; this implementation uses Blade + Alpine for lightweight migration.
- **UI system:** Tailwind CSS + Headless UI patterns.
- **Media:** S3-compatible object storage with signed URLs; local disk used in this demo.
- **Performance:** Redis queues + Horizon, Octane (Swoole/RoadRunner), DB indexing and cursor pagination.
- **Deployment:** Forge (VM) or Vapor (serverless), TLS everywhere, WSS-only in production.

## Implemented in this repository

- Multi-room chat schema (`chat_rooms`, `chat_room_user`, `messages`, `message_reactions`, `message_read_receipts`).
- Cursor-based message pagination and infinite scroll endpoint.
- Typing indicator events with debounce-friendly endpoint.
- Presence heartbeat endpoint for online/offline state.
- Read receipts with delivered/seen timestamps.
- Emoji reactions per message.
- Attachment upload endpoint (image/document).
- Optimistic message rendering in UI.
- Skeleton loading state in UI.

## Reverb/Soketi setup notes

1. For **Laravel 11 + Reverb**:
   - Install: `composer require laravel/reverb`
   - Publish config and set `BROADCAST_CONNECTION=reverb`.
   - Route auth via `/broadcasting/auth` and private channels `chat.room.{id}`.
2. For **Soketi**:
   - Keep `BROADCAST_CONNECTION=pusher`.
   - Point `PUSHER_HOST`, `PUSHER_PORT`, `PUSHER_SCHEME` to Soketi.
3. Enforce WSS in production and disable plaintext WS outside local dev.

## Security & scale checklist

- Add per-user send throttles via `RateLimiter` (messages/minute).
- Encrypt sensitive payloads (application-level encryption for private rooms).
- Queue heavy tasks: notifications, previews, virus scans.
- Add compound indexes for unread scans and room timelines (already included in migrations).
- Move uploads to S3 and serve via CloudFront.

