# Fix: Metro Bundler Crashed on pusher-js sourceMappingURL Comment

**Date:** 2026-05-08
**Files Changed (mobile app):**
- `scripts/strip-all-sourcemaps.js` (new)
- `package.json` (postinstall hook)
- `metro.config.js` (reverted to minimal config)

---

## What Was Broken
After installing `pusher-js`, the Metro bundler crashed with:

```
error: Unable to resolve module `//# sourceMappingURL=pusher.js.map`
```

The app refused to build entirely.

## Root Cause
Metro's transform layer reads `//# sourceMappingURL=` comments in JS files and tries to resolve the path as a module dependency. The minified `pusher-js` react-native build (`node_modules/pusher-js/dist/react-native/pusher.js`) and several `laravel-echo/dist/*.js` files all end with these comments.

Approaches that **don't** work:
- `resolver.blockList` — runs after transform, too late
- `resolver.resolveRequest` — only fires for real module imports, not sourcemap comments
- `BABEL_ENV` tricks — sourcemap stripping is not in Babel's pipeline at this stage

The only reliable fix is to physically remove the comments from the dist files before Metro sees them.

## What Was Changed

### `scripts/strip-all-sourcemaps.js` (new file)
Node script that reads each affected dist file, strips lines matching `/^\/\/# sourceMappingURL=/`, and writes the file back. Targets:
- `node_modules/pusher-js/dist/react-native/pusher.js`
- `node_modules/laravel-echo/dist/*.js` (glob)

### `package.json`
Added a `postinstall` hook so the script runs automatically every time `npm install` is run:
```json
"scripts": {
  "postinstall": "node scripts/strip-all-sourcemaps.js"
}
```

### `metro.config.js`
Reverted to the default empty config — none of the resolver overrides were needed or effective.

---

## How to Test

1. Delete `node_modules/` and run `npm install`
2. Confirm the postinstall script runs without errors
3. Run `npx react-native run-android` — Metro should bundle without the sourceMappingURL error
4. If the error reappears after an `npm install`, check the script output — a new version of pusher-js or laravel-echo may have changed its dist file path

## Watch Out For
- **Every `npm install` re-runs the strip script** — this is intentional; package updates can restore the original files
- If `pusher-js` releases a version with sourcemap comments already removed, the script is a no-op and safe to leave
- Do not delete `scripts/strip-all-sourcemaps.js` — the postinstall hook will fail silently if the file is missing (check your `package.json` postinstall line)
