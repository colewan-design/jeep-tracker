# Weekly Code Audit — JeepTracker

> Run this once a week. Go through each section, check the items, log findings at the bottom.

---

## Audit Info

- **Date:**
- **Auditor:** Christian
- **Branch:**
- **Last audit:**

---

## 1. Crash & Error Check

- [ ] Run `adb logcat -s ReactNativeJS AndroidRuntime` while using the app
- [ ] Check for any unhandled promise rejections
- [ ] Check for any `console.warn` / `console.error` that shouldn't be there
- [ ] Open driver screen → start tracking → confirm no crash
- [ ] Open passenger screen → select jeep → confirm map loads

**Findings:**

---

## 2. API & Network

- [ ] Driver location POST is reaching the backend (check Laravel logs or DB)
- [ ] Passenger location GET returns correct structure `{ location: { latitude, longitude } }`
- [ ] Auth token is being attached to all requests
- [ ] Expired token is handled gracefully (logs out instead of hanging)

**Findings:**

---

## 3. Location & GPS

- [ ] `watchPosition` fires within ~10s of pressing Start Tracking
- [ ] Location updates stop when Stop Tracking is pressed
- [ ] `clearWatch` is called on component unmount (no memory leaks)
- [ ] Passenger's own location is acquired on screen load

**Findings:**

---

## 4. UI & UX

- [ ] Login screen renders correctly on different screen sizes
- [ ] Driver jeep list scrolls if there are many jeeps
- [ ] Passenger map fits both markers in view
- [ ] Info bar at the bottom shows correct distance / speed / time
- [ ] No layout overflow or clipped text

**Findings:**

---

## 5. Dependencies

- [ ] Run `npm audit` — check for high/critical vulnerabilities
- [ ] Check if any packages have newer stable versions relevant to known bugs
- [ ] Confirm `@react-native-async-storage/async-storage` is on `^2.x` (not v3)
- [ ] Confirm `react-native-geolocation-service` is NOT in package.json (replaced)

**Findings:**

---

## 6. Build

- [ ] `.\gradlew assembleRelease` completes without errors
- [ ] APK installs and launches without crash
- [ ] No new deprecation warnings that could become errors in the next RN version

**Findings:**

---

## Known Ongoing Issues

| Issue | Status | Notes |
|-------|--------|-------|
| New Architecture forced (RN 0.85) | Monitoring | Some libs show legacy arch warnings |
| Google Maps API key unrestricted | Pending | Tried restricting — failed, revisit |

---

## Action Items from This Audit

- [ ]
- [ ]
- [ ]
