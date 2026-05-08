# Feature: Driver Jeep Management, Breadcrumb Trail, Animated Marker, Offline Status

**Date:** 2026-05-08
**Files Changed:**
- `app/Events/JeepStatusChanged.php` (new)
- `app/Http/Controllers/Api/JeepController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Models/Jeep.php`
- `app/Models/User.php`
- `database/migrations/2026_05_08_071013_add_user_id_to_jeeps_table.php` (new)
- `src/screens/DriverScreen.js` (mobile)
- `src/screens/PassengerScreen.js` (mobile)
- `src/utils/echo.js` (mobile)

---

## What Was Added

### Driver: Add and Manage Own Jeeps
Drivers previously had to rely on seed/admin data. Now they can create their own jeep from the app:
- "My Jeeps" section with a **+ Add** button opens a modal form (name, plate number, route name, capacity)
- `POST /api/jeeps` creates the jeep with `user_id = auth()->id()` and `status = inactive`
- The new jeep is appended to the list and auto-selected
- An empty-state card is shown if the driver has no jeeps yet

`GET /api/jeeps` now returns only the driver's own jeeps (role check in `JeepController@index`).

### Driver: Stop Tracking Sets Jeep Inactive
When the driver taps "Stop Tracking", the app now calls `PATCH /api/jeeps/{id}` with `{ status: 'inactive' }` before clearing the GPS watch. This triggers the `JeepStatusChanged` broadcast event (see below).

### Driver: GPS Only Fires When Moving
`distanceFilter: 10` added to `Geolocation.watchPosition` — the GPS callback (and therefore every location POST to the API) only fires after the device moves at least 10 meters. Prevents flooding the database with duplicate coordinates while stationary.

### Backend: JeepStatusChanged Broadcast Event
New event `app/Events/JeepStatusChanged.php`:
- Broadcasts on `jeep.{id}` (same channel as location updates)
- Event name on the wire: `status.changed`
- Payload: `{ jeep_id, status }`
- Fired by `JeepController@update` whenever `status` is included in the request

### Backend: Logout Sets All Jeeps Inactive
`AuthController@logout` now calls `$request->user()->jeeps()->update(['status' => 'inactive'])` before deleting the token. Ensures passengers see the jeep go offline if a driver logs out without explicitly stopping tracking.

### Passenger: Road-Following Breadcrumb Trail
- `fetchHistory` loads the last 60 GPS points from `GET /api/jeeps/{id}/location/history` and stores them as `jeepTrail`
- A `Polyline` is drawn through the points — because points follow actual roads traveled, the line naturally follows roads (same principle as Strava/Google Maps)
- Trail updates in real time: each `.location.updated` WebSocket event appends to the trail (capped at 60 points)

### Passenger: Animated Jeep Marker
- `jeepMarkerRef` ref attached to the jeep `Marker`
- On each WebSocket location update: `jeepMarkerRef.current.animateMarkerToCoordinate(coord, 1000)` smoothly glides the marker to the new position over 1 second instead of jumping

### Passenger: Real-Time Offline / Live Status
- `jeepActive` state tracks whether the selected jeep is currently broadcasting
- Set to `true` on every location update; set via `status.changed` WebSocket event
- Info bar LIVE indicator reacts:
  - **Active:** green dot + "LIVE" + timestamp in white
  - **Offline:** red dot + "OFFLINE" + timestamp grayed out

---

## How to Test

### Driver flow
1. Log in as a driver
2. Tap **+ Add** → fill in jeep details → submit
3. Confirm the jeep appears in the list and is auto-selected
4. Tap **Start Tracking** — GPS should start posting locations
5. Tap **Stop Tracking** — app should call `PATCH /api/jeeps/{id}` with `{ status: 'inactive' }`

### Passenger flow
1. Log in as a passenger
2. Select an active jeep chip — a jeep marker should appear immediately (from current location fetch)
3. The breadcrumb trail (blue polyline) should appear after ~1 second (from history fetch)
4. Watch the marker animate smoothly as the driver moves
5. Have the driver stop tracking — the info bar should change from green LIVE to red OFFLINE within seconds

### Database migration
```bash
php artisan migrate
```
Adds nullable `user_id` foreign key to `jeeps` table.

## Watch Out For
- `animateMarkerToCoordinate` only works on Android with `react-native-maps` — on iOS use `Animated.timing` or accept a static marker
- The history endpoint returns newest-first — the mobile app `.reverse()`s the array before storing it to get chronological order for the Polyline
- `distanceFilter: 10` means the first location post after opening the app may be delayed until the driver moves 10m — acceptable tradeoff vs database spam
- `JeepStatusChanged` is fired on **any** status change via `update()`, including manual admin edits — this is intentional
