# FIX-004: Replaced setInterval + getCurrentPosition with watchPosition

## Problem
Logcat showed non-stop GPS timeouts:
```
W ReactNativeJS: 'GPS error:', 'Location request timed out'
```
No location was ever sent to the backend. The passenger screen always showed "waiting".

## Root Cause
Two problems combined:

1. `enableHighAccuracy: true` requires a full GPS satellite fix which fails indoors and
   takes too long outdoors. The 10s timeout was not enough.

2. `setInterval(sendLocation, 5000)` fires a new `getCurrentPosition` every 5 seconds,
   but each request can take up to 10 seconds. This stacks up concurrent GPS requests
   that pile up and all time out.

## What Changed
- File: `src/screens/DriverScreen.js`

  Before:
  ```js
  intervalRef.current = setInterval(sendLocation, 5000);
  // inside sendLocation:
  Geolocation.getCurrentPosition(..., { enableHighAccuracy: true, timeout: 10000 })
  ```

  After:
  ```js
  watchRef.current = Geolocation.watchPosition(
    async pos => { ... },
    err => console.warn(err),
    { enableHighAccuracy: false, distanceFilter: 0, interval: 5000 }
  );
  ```

  Also changed `clearInterval` → `Geolocation.clearWatch` in `stopTracking`.

## How to Test
1. Log in as driver, select jeep, press Start Tracking
2. Wait ~10 seconds indoors
3. Coordinates should appear on screen (lat/lng/speed)
4. Check logcat — should see NO "GPS error: timed out" messages
5. Log in as passenger, select same jeep → should show live location

## Notes
- `distanceFilter: 0` means it fires even when stationary — good for testing
- For production, consider changing to `distanceFilter: 10` (only update after 10m movement)
- `enableHighAccuracy: false` uses network/cell tower location, sufficient for vehicle tracking
