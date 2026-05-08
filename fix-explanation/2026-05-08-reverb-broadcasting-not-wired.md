# Fix: Laravel Reverb Broadcasting Not Working

**Date:** 2026-05-08
**Files Changed:**
- `.env`
- `app/Events/JeepLocationUpdated.php` (new)
- `routes/channels.php` (new)
- `bootstrap/app.php`
- `app/Http/Controllers/Api/LocationController.php`

---

## What Was Broken
Reverb was installed but real-time jeep location updates were never actually broadcast to connected clients. Drivers could post location updates and they'd save to the database — but nothing was sent over WebSocket.

## Root Cause
Five separate things were all missing at once:
1. `BROADCAST_DRIVER` was set to `log` (writes to log file, not Reverb)
2. `REVERB_HOST` pointed to `localhost` instead of the actual domain — remote clients can never reach `localhost` of a server
3. No event class existed to carry the broadcast payload
4. No `routes/channels.php` to authorize channel subscriptions
5. `LocationController` never dispatched any event

## What Was Changed

### `.env`
- `BROADCAST_DRIVER=log` → `BROADCAST_DRIVER=reverb`
- `REVERB_HOST="localhost"` → `REVERB_HOST="jeep-tracker.eishipartners.com"`

### `app/Events/JeepLocationUpdated.php` (new file)
Created the broadcast event. It:
- Implements `ShouldBroadcast`
- Broadcasts on public channel `jeep.{id}` — one channel per jeep
- Event name on the wire: `location.updated`
- Payload includes jeep info + full location fields

### `routes/channels.php` (new file)
Registers the `jeep.{jeepId}` channel. Set to public (`return true`) since location data is not sensitive — any client tracking a jeep should be able to subscribe.

### `bootstrap/app.php`
Added `channels: __DIR__.'/../routes/channels.php'` to `withRouting()`. Without this Laravel never loads the channels file and all channel auth requests return 403.

### `app/Http/Controllers/Api/LocationController.php`
Added `broadcast(new JeepLocationUpdated($jeep, $location))` right after the DB save and jeep status update. Also added the import at the top.

---

## How to Test

### On the server first:
```bash
php artisan reverb:start
```
Make sure port 8080 is open in your firewall/server config.

### Test the broadcast:

1. Open a WebSocket client (or use Laravel Echo in the frontend) and subscribe to channel `jeep.1`
2. POST to `https://jeep-tracker.eishipartners.com/api/jeeps/1/location` with:
   ```json
   {
     "latitude": 16.4090,
     "longitude": 120.5930,
     "speed": 20,
     "heading": 90,
     "accuracy": 10
   }
   ```
   Include your Bearer token in the Authorization header.
3. The WebSocket client should immediately receive a `location.updated` event with the jeep + location payload.

### Quick local test with Tinker:
```bash
php artisan tinker
>>> broadcast(new \App\Events\JeepLocationUpdated(\App\Models\Jeep::first(), \App\Models\JeepLocation::first()));
```
Should not throw — if it does, check `REVERB_APP_KEY` and that `reverb:start` is running.

---

## Watch Out For

- **Port 8080 must be open** on the server. If behind Nginx/Apache, you may need to proxy WebSocket connections or open the port in the firewall.
- **HTTPS/WSS**: Since the app runs on `https://`, browsers will block mixed content (wss vs ws). If clients connect via `wss://`, Reverb needs SSL too — either configure a reverse proxy or set `REVERB_SCHEME=https`.
- **Queue driver is `sync`** — broadcasts fire immediately inline with the request. Fine for now, but if location updates get frequent, switch `QUEUE_CONNECTION=database` or `redis` to avoid slowing down the HTTP response.
- **Channel is public** — if you later need to restrict who can track a jeep (e.g. only authenticated users), change `routes/channels.php` to return `Auth::check()` or a role check.
