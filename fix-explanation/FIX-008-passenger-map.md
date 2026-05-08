# FIX-008: Added Live Map to Passenger Screen with Distance

## What Was Added
The passenger screen was showing only coordinates. Now it shows a full-screen map with:
- Passenger's own location (blue pulsing dot)
- Jeep's live location (🚐 marker)
- Dashed blue line connecting the two
- Bottom info bar: jeep name, speed, distance to jeep, last update time

## How It Works

### Passenger Location
Uses `Geolocation.watchPosition` to continuously track the passenger's own GPS position.
Starts automatically when the screen loads (after requesting permission).

### Jeep Location
Same polling as before — `GET /jeeps/{id}/location` every 5 seconds.

### Distance Calculation
Uses the **Haversine formula** — calculates straight-line distance between two GPS
coordinates accounting for Earth's curvature.
- Under 1km: shows in meters (e.g. "340 m")
- Over 1km: shows in kilometers (e.g. "2.3 km")

### Map Auto-Fit
When both locations are known, the map automatically calls `fitToCoordinates` to fit
both markers in view with padding. When only the jeep is known, it animates to the jeep.

## Files Changed
- `src/screens/PassengerScreen.js` — full rewrite with MapView
- `android/app/src/main/AndroidManifest.xml` — added Google Maps API key (see FIX-006)

## How to Test
1. Log in as passenger
2. Grant location permission when prompted
3. Select a jeep that has an active driver tracking
4. Map should show:
   - Blue dot = your position
   - 🚐 = jeep position
   - Dashed line between them
5. Bottom bar should show distance (e.g. "1.2 km") updating every 5 seconds
6. Map should auto-zoom to show both markers

## Notes
- Requires Google Maps API key (FIX-006) and Maps SDK for Android enabled in GCP
- `distanceFilter: 10` on passenger watch — updates after moving 10 meters
- The dashed line is straight (as the crow flies), not a road route
