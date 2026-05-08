# FIX-006: Added Google Maps API Key to AndroidManifest

## Problem
App crashed on startup after adding react-native-maps to the PassengerScreen.
No error appeared in logcat. The map section showed a white screen when it didn't crash.

## Root Cause
`react-native-maps` on Android always uses Google Maps SDK under the hood — even without
explicitly setting `PROVIDER_GOOGLE`. The Google Maps SDK initializes at app startup and
requires a valid API key in the manifest. Without it, the app crashes before reaching
the login screen.

## What Changed
- File: `android/app/src/main/AndroidManifest.xml`

  Added inside `<application>` tag:
  ```xml
  <meta-data
    android:name="com.google.android.geo.API_KEY"
    android:value="AIzaSyCsiDYzoVEx6oSReXcY93EZMBSZizMT_KE"/>
  ```

- File: `src/screens/PassengerScreen.js`
  Change: Re-added `PROVIDER_GOOGLE` to MapView now that key is set

## How to Test
1. Install the APK
2. App should open without crashing
3. Log in as passenger
4. Select a jeep — map should render (not white)
5. Your location (blue dot) should appear after granting location permission

## Notes
- The Maps SDK for Android must also be **enabled** in Google Cloud Console
  under APIs & Services → Library → "Maps SDK for Android"
- The API key restriction (by package name + SHA-1) failed during setup
  — revisit this in Google Cloud Console to prevent unauthorized key usage
- Key is currently unrestricted — restrict it when the restriction UI works
