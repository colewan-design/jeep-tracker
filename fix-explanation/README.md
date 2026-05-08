# Fix Explanation Logs

Every time a fix is applied, a log file is created here.
Read these to understand what changed, why, and how to test it.

---

## Log Index

| File | Fix Summary | Date |
|------|-------------|------|
| [FIX-001-async-storage-downgrade.md](./FIX-001-async-storage-downgrade.md) | Downgraded async-storage v3 → v2 | 2026-05-07 |
| [FIX-002-new-arch-disabled.md](./FIX-002-new-arch-disabled.md) | Disabled New Architecture (later reverted) | 2026-05-07 |
| [FIX-003-geolocation-library-swap.md](./FIX-003-geolocation-library-swap.md) | Replaced react-native-geolocation-service with @react-native-community/geolocation | 2026-05-07 |
| [FIX-004-watchposition-gps-timeout.md](./FIX-004-watchposition-gps-timeout.md) | Replaced setInterval+getCurrentPosition with watchPosition | 2026-05-07 |
| [FIX-005-passenger-location-response.md](./FIX-005-passenger-location-response.md) | Fixed passenger screen reading wrong API response field | 2026-05-07 |
| [FIX-006-google-maps-api-key.md](./FIX-006-google-maps-api-key.md) | Added Google Maps API key to AndroidManifest | 2026-05-07 |
| [FIX-007-ui-redesign.md](./FIX-007-ui-redesign.md) | Full UI redesign — modern dark navy theme | 2026-05-07 |
| [FIX-008-passenger-map.md](./FIX-008-passenger-map.md) | Added live map to passenger screen with distance | 2026-05-07 |

---

## File Naming Convention

```
FIX-[number]-[short-kebab-description].md
```

## Log Template

```markdown
# FIX-NNN: Short Title

## Problem
What was broken and how it showed up.

## Root Cause
Why it was broken.

## What Changed
- File: `src/...`  
  Change: description

## How to Test
Step by step to confirm the fix works.

## Notes
Anything worth remembering.
```
