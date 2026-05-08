# Fix: Passengers Couldn't See Any Jeeps on the Map

**Date:** 2026-05-08
**Files Changed:**
- `app/Http/Controllers/Api/JeepController.php`

---

## What Was Broken
Passengers logged in and saw a completely empty jeep list — no chips to select, no marker on the map. Drivers could see their own jeep fine.

## Root Cause
`JeepController@index` filtered by `user_id` for **every** role:

```php
$query = Jeep::with('latestLocation');
$query->where('user_id', $user->id); // applied unconditionally
```

Passengers have no jeeps assigned to them, so the query returned nothing. The original intent was to scope the list for drivers, but the role check was missing.

## What Was Changed

### `app/Http/Controllers/Api/JeepController.php`
- **What:** wrapped the `where('user_id')` clause in a driver-only role check
- **Before:**
  ```php
  $query = Jeep::with('latestLocation');
  $query->where('user_id', $user->id);
  return response()->json($query->get());
  ```
- **After:**
  ```php
  $query = Jeep::with('latestLocation');
  if ($user->role === 'driver') {
      $query->where('user_id', $user->id);
  }
  return response()->json($query->get());
  ```
- **Why:** drivers should only see their own jeeps (to manage them); passengers should see all jeeps (to track any of them)

---

## How to Test

1. Log in as a **passenger** and call `GET /api/jeeps` — should return all jeeps in the database
2. Log in as a **driver** and call `GET /api/jeeps` — should return only jeeps where `user_id` matches the driver's id
3. Open the passenger screen in the app — jeep chips should appear in the list and the map marker should show when one is selected

## Watch Out For
- Any new role added in the future (e.g. admin) defaults to seeing **all** jeeps — add an explicit case if that needs restricting
- If a jeep has `user_id = null` (old seed data), it is visible to passengers but invisible to all drivers — run `UPDATE jeeps SET user_id = <driver_id>` to assign ownership
