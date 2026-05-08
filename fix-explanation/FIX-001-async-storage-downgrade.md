# FIX-001: Downgraded async-storage v3 → v2

## Problem
Build failed immediately with:
```
Could not find org.asyncstorage.shared_storage:storage-android:1.0.0
```

## Root Cause
`@react-native-async-storage/async-storage` v3.x uses Kotlin Multiplatform and publishes
a new Maven artifact (`org.asyncstorage.shared_storage:storage-android`) that is not hosted
in the standard Google or Maven Central repositories configured in the project.

## What Changed
- File: `package.json`
  Change: `"@react-native-async-storage/async-storage": "^3.0.2"` → `"^2.1.2"`

## How to Test
1. Run `npm install`
2. Run `.\gradlew assembleRelease` — should complete without the missing artifact error
3. Install and open the app — login should persist across restarts (AsyncStorage working)

## Notes
v2.x is the current stable release and fully compatible with RN 0.85.
Do not upgrade to v3 until it officially supports standard Maven repos.
