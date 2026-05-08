# FIX-003: Replaced react-native-geolocation-service with @react-native-community/geolocation

## Problem
App crashed immediately when pressing Start Tracking:
```
FATAL EXCEPTION: mqt_v_native
java.lang.IncompatibleClassChangeError: Found interface
com.google.android.gms.location.FusedLocationProviderClient, but class was expected
```

## Root Cause
`react-native-geolocation-service` v5.3.1 was compiled against a version of Google Play
Services location where `FusedLocationProviderClient` was a class. At runtime, another
dependency (`react-native-maps`) brought in a different version where it's defined as an
interface. This binary mismatch causes a fatal JVM error.

Attempts to fix with `resolutionStrategy` forcing `play-services-location:21.3.0` and
`20.0.0` did not resolve the conflict because the mismatch is baked into the compiled AAR.

Additionally, RN 0.85 forces New Architecture (the `newArchEnabled=false` setting has been
ignored since RN 0.82), and `react-native-geolocation-service` v5.3.1 does not fully
support New Architecture.

## What Changed
- File: `package.json`
  Change: removed `"react-native-geolocation-service": "^5.3.1"`, added `"@react-native-community/geolocation": "^3.4.0"`
- File: `src/screens/DriverScreen.js`
  Change: import changed from `react-native-geolocation-service` to `@react-native-community/geolocation`
- File: `android/gradle.properties`
  Change: removed `newArchEnabled=false` (was being ignored anyway, cleaned up)

## How to Test
1. Log in as driver
2. Select a jeep
3. Press Start Tracking
4. App should NOT crash — coordinates should appear on screen within 10-15 seconds

## Notes
`@react-native-community/geolocation` uses Android's standard LocationManager API,
avoiding the FusedLocation dependency conflict entirely.
