# FIX-005: Fixed Passenger Screen Reading Wrong API Response Field

## Problem
Driver was successfully sending location, database had records, but passenger screen
always showed "Waiting for [jeep] to share location..." and never updated.

## Root Cause
The Laravel `LocationController@current` endpoint returns:
```json
{
  "jeep": { "id": 1, "name": "Happy Hallow", ... },
  "location": { "latitude": 16.123, "longitude": 120.456, ... }
}
```

But the passenger screen was checking:
```js
const location = data?.data ?? data;   // looks for data.data or data
if (location?.latitude) { ... }        // then checks .latitude at top level
```

`data.latitude` is undefined because latitude is nested under `data.location`.
The check `data?.data` also fails because the response has no `.data` wrapper.
So `jeepLocation` was never set and the waiting state never cleared.

## What Changed
- File: `src/screens/PassengerScreen.js`

  Before:
  ```js
  const location = data?.data ?? data;
  if (location?.latitude) {
  ```

  After:
  ```js
  const location = data?.location;
  if (location?.latitude) {
  ```

## How to Test
1. Start driver tracking (see FIX-004)
2. Log in as passenger, select the same jeep
3. Within 5 seconds the "waiting" message should disappear and coordinates should appear
4. The map should show the jeep marker and your location

## Notes
Always check the actual API response structure before assuming field names.
The `fetchJeeps` function correctly used `data.data ?? data` because the jeeps endpoint
wraps in `.data`. The location endpoint does not — it uses `.location`.
