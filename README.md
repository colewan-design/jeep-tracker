# Jeep Tracker — Mobile App

React Native app for real-time jeepney tracking. Drivers share their live GPS location; passengers watch it on a map with a breadcrumb trail and animated marker.

---

## Architecture

```
src/
├── api/axios.js          — Axios instance pointed at the API base URL
├── context/AuthContext.js — Login state, token storage, role routing
├── screens/
│   ├── LoginScreen.js
│   ├── DriverScreen.js   — GPS tracking, jeep management
│   └── PassengerScreen.js — Live map, trail, offline status
└── utils/echo.js         — Laravel Echo + Pusher WebSocket setup
```

**Backend:** Laravel 11 API at `https://jeep-tracker.eishipartners.com/api`  
**WebSocket:** Pusher (cluster `ap1`) — channel `jeep.{id}`

---

## Roles

| Role | What they see |
|------|---------------|
| Driver | Their own jeeps only. Can add jeeps, start/stop GPS tracking. |
| Passenger | All jeeps. Can select one to track on the map. |

---

## Driver Screen

- Lists jeeps owned by the driver (`GET /api/jeeps`)
- **+ Add** button opens a modal to create a new jeep (name, plate, route, capacity) — posts to `POST /api/jeeps`
- **Start Tracking** requests GPS permission and begins `watchPosition` with `distanceFilter: 10` (only fires after 10m of movement)
- Each GPS update posts to `POST /api/jeeps/{id}/location` — the backend broadcasts `location.updated` to Pusher
- **Stop Tracking** calls `PATCH /api/jeeps/{id}` with `{ status: 'inactive' }` then clears the GPS watch — triggers `status.changed` broadcast so passengers go offline immediately

---

## Passenger Screen

- Fetches all jeeps on mount (`GET /api/jeeps`)
- Selecting a chip runs a two-phase fetch:
  1. `GET /api/jeeps/{id}/location` — shows the marker immediately
  2. `GET /api/jeeps/{id}/location/history?limit=60` — loads the last 60 GPS points to draw the breadcrumb trail
- Subscribes to Pusher channel `jeep.{id}`:
  - `location.updated` → appends to trail, calls `animateMarkerToCoordinate` for smooth glide
  - `status.changed` → updates online/offline indicator
- Info bar shows jeep name, speed, distance to passenger, and a LIVE/OFFLINE status dot

---

## WebSocket Setup (`src/utils/echo.js`)

```js
import Echo from 'laravel-echo';
import PusherModule from 'pusher-js';
const Pusher = PusherModule.Pusher ?? PusherModule;
global.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: '0f12289adb56149777a9',
  cluster: 'ap1',
  forceTLS: true,
});
```

`pusher-js` exports the class as a named export (`module.exports.Pusher`), not the default. The `?? PusherModule` fallback handles version differences.

---

## Known Build Quirk — sourceMappingURL

Metro tries to resolve `//# sourceMappingURL=pusher.js.map` comments as module imports and crashes. A postinstall script strips these comments from the pusher-js and laravel-echo dist files:

```
scripts/strip-all-sourcemaps.js
```

Runs automatically via `"postinstall": "node scripts/strip-all-sourcemaps.js"` in `package.json`. Re-runs on every `npm install` in case package updates restore the originals.

See `fix-explanation/2026-05-08-metro-sourcemap-bundler-error.md` in the API repo for full details.

---

## Building

```bash
npm install          # also runs postinstall strip script
npx react-native run-android
```

Release APK:
```bash
cd android
./gradlew assembleRelease
# APK at android/app/build/outputs/apk/release/app-release.apk
```

---

## Environment

The API base URL is set in `src/api/axios.js`. Update it if the server changes:

```js
baseURL: 'https://jeep-tracker.eishipartners.com/api',
```
