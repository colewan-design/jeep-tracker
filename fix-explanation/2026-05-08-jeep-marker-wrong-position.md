# Fix: Jeep Marker Appearing at Wrong Position on Passenger Map

**Date:** 2026-05-08
**Files Changed:**
- `src/screens/PassengerScreen.js` (mobile app)

---

## What Was Broken
The jeep marker on the passenger map was appearing in the wrong location. The map also panned/zoomed to the wrong area when a jeep was selected.

## Root Cause
The API returns `latitude` and `longitude` as **strings** (e.g. `"16.4090"`) in all location responses — history endpoint, current location endpoint, and Pusher WebSocket payload. The app stored these raw objects directly into `jeepLocation` state.

`fitToCoordinates` (used to zoom the map to show both the jeep and the passenger) received string coordinates:
```js
{ latitude: "16.4090", longitude: "120.5930" }  // ❌ strings
```
React Native Maps silently misbehaves with string coordinates, causing incorrect map positioning.

The `Marker` component had its own `parseFloat` call so the pin rendered correctly, but the map camera was zooming to the wrong place.

## What Was Changed

### `src/screens/PassengerScreen.js`

Added a `normalizeLoc()` helper:
```js
function normalizeLoc(loc) {
  return { ...loc, latitude: parseFloat(loc.latitude), longitude: parseFloat(loc.longitude) };
}
```

Applied it at every `setJeepLocation` call site:
- Phase 1 fetch (current location): `setJeepLocation(normalizeLoc(data.location))`
- Phase 2 fetch (history): `setJeepLocation(normalizeLoc(data.locations[0]))`
- WebSocket `.location.updated`: `setJeepLocation(normalizeLoc(payload.location))`

Removed all the scattered `parseFloat` calls in the render (marker coordinate, haversine distance, `animateToRegion`) since the state value is now always a number.

---

## How to Test

1. Log in as a passenger and select a jeep chip
2. The map should pan/zoom to the jeep's actual GPS coordinates
3. If both jeep and passenger locations are known, the map should fit both markers on screen
4. Move the driver — the marker should animate to the correct new position

## Watch Out For
- Any new place that calls `setJeepLocation` must also call `normalizeLoc()` — otherwise the bug silently comes back
- The API will always return string lat/lng (Laravel casts numeric DB columns as strings in JSON) — do not rely on the API to fix this
