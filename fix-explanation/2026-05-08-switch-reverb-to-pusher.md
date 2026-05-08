# Fix: Switched Broadcasting from Reverb to Pusher

**Date:** 2026-05-08
**Files Changed:**
- `.env`
- `composer.json` / `composer.lock` (pusher/pusher-php-server added)

---

## What Was Broken
Laravel Reverb was configured and wired up correctly, but it couldn't run on Hostinger shared hosting because:
1. Port 8080 is blocked by Hostinger's firewall — external clients can't connect
2. Reverb requires a persistent background process (`php artisan reverb:start`) which shared hosting kills

## Root Cause
Reverb runs its own WebSocket server on a custom port (8080). Shared hosting only exposes ports 80 and 443. No amount of config changes fixes this — it's a hosting infrastructure limitation.

## What Was Changed

### `.env`
Replaced all Reverb variables with Pusher credentials:

```
# Before
BROADCAST_DRIVER=reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_HOST=...
...

# After
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=2152051
PUSHER_APP_KEY=0f12289adb56149777a9
PUSHER_APP_SECRET=b0debc3d40d33589a281
PUSHER_APP_CLUSTER=ap1
PUSHER_SCHEME=https
```

### `composer.json`
Added `pusher/pusher-php-server` — the PHP SDK Laravel uses to send events to Pusher's servers.

```bash
composer require pusher/pusher-php-server
```

### Nothing else changed
The `JeepLocationUpdated` event, `LocationController`, `routes/channels.php`, and `bootstrap/app.php` are all untouched — Laravel's broadcasting abstraction means Pusher is a drop-in replacement for Reverb.

---

## How Pusher Works (vs Reverb)
- **Reverb:** your server runs the WebSocket server — clients connect directly to your server on port 8080
- **Pusher:** your server sends events to Pusher's cloud over HTTPS (port 443) — clients connect to Pusher's servers instead of yours

No process to start, no port to open.

---

## How to Test

1. Go to https://dashboard.pusher.com → your `jeep-tracker` app → **Debug Console** tab
2. Hit `POST https://jeep-tracker.eishipartners.com/api/jeeps/1/location` with:
   ```json
   {
     "latitude": 16.4090,
     "longitude": 120.5930,
     "speed": 20,
     "heading": 90,
     "accuracy": 10
   }
   ```
   Include Bearer token in Authorization header.
3. You should see a `location.updated` event appear live in the Pusher Debug Console on channel `jeep.1`.

---

## Pusher Account Details
- **App name:** jeep-tracker
- **Cluster:** ap1 (Singapore)
- **Free tier limits:** 200k messages/day, 100 simultaneous connections
- **Dashboard:** https://dashboard.pusher.com

## Watch Out For
- **Secret is in `.env` only** — never commit `PUSHER_APP_SECRET` to GitHub
- **If you hit free tier limits**, check the Pusher dashboard for usage stats — upgrade or optimize broadcast frequency
- **Frontend** must use Pusher JS SDK (not Laravel Echo with Reverb config) — cluster must be `ap1`
